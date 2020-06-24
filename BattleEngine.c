/*
 * Copyright (C) 2020 Patryk Stefanski
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

#include <assert.h>
#include <inttypes.h>
#include <math.h>
#include <stdbool.h>
#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#if defined _MSC_VER && _MSC_VER >= 1900
#  define restrict __restrict
#elif defined _MSC_VER
#  define restrict
#endif

/* Lehmer RNG. */
#define RANDOM_MULTIPLIER 48271UL
#define RANDOM_MODULUS 2147483647UL
#define RANDOM_MAX (RANDOM_MODULUS - 1)
#define RANDOM_NEXT(r)                                                         \
  ((uint32_t)((uint64_t)(r)*RANDOM_MULTIPLIER % RANDOM_MODULUS))

#define MAX_ROUNDS 6

struct unit_attributes {
  float weapons;
  float shield;
  float armor;
  uint32_t *rapid_fire;
};

struct units_attributes {
  uint8_t num_kinds;
  uint32_t *rapid_fire;
  struct unit_attributes attributes[];
};

/* Unit group = units with the same kind. */
struct unit_group_stats {
  uint64_t times_fired;
  uint64_t times_was_shot;
  uint64_t shield_damage_dealt;
  uint64_t hull_damage_dealt;
  uint64_t shield_damage_taken;
  uint64_t hull_damage_taken;
  uint64_t num_remaining_units;
};

struct combatant {
  struct unit_group_stats *stats;
  uint8_t weapons_technology;
  uint8_t shielding_technology;
  uint8_t armor_technology;
  uint64_t *unit_groups;
};

struct unit {
  float shield;
  float hull;
  uint8_t kind;
  uint8_t combatant_id;
};

struct party {
  struct combatant *combatants;
  struct unit *units;
  uint64_t num_alive;
};

const uint64_t MAX_UNITS = UINT64_MAX / sizeof(struct unit);

static struct units_attributes *load_units_attributes(FILE *file) {
  int n;

  uint8_t num_kinds;
  n = fscanf(file, "%" SCNu8, &num_kinds);
  if (n != 1) {
    fputs("Parsing units attributes failed, cannot scan num_kinds\n", stderr);
    goto fail;
  }

  if (num_kinds == 0) {
    fputs("Parsing units attributes failed, num_kinds must be greater than 0\n",
          stderr);
    goto fail;
  }

  struct units_attributes *units_attributes =
      malloc(sizeof(*units_attributes) +
             num_kinds * sizeof(*units_attributes->attributes));
  if (units_attributes == NULL) {
    fputs("Parsing units attributes failed, allocation of attributes failed\n",
          stderr);
    goto fail;
  }

  uint32_t *rapid_fire =
      calloc((size_t)num_kinds * (size_t)num_kinds, sizeof(*rapid_fire));
  if (rapid_fire == NULL) {
    fputs("Parsing units attributes failed, allocation of rapid fire failed\n",
          stderr);
    goto fail_units_attributes;
  }

  units_attributes->num_kinds = num_kinds;
  units_attributes->rapid_fire = rapid_fire;

  for (uint8_t kind = 0; kind < num_kinds; kind++) {
    struct unit_attributes *attr = &units_attributes->attributes[kind];
    attr->rapid_fire = rapid_fire + (size_t)kind * (size_t)num_kinds;

    uint8_t num_rapid_fire;
    n = fscanf(file, "%f%f%f%" SCNu8, &attr->weapons, &attr->shield,
               &attr->armor, &num_rapid_fire);
    if (n != 4) {
      fprintf(stderr,
              "Parsing units attributes failed, cannot scan kind #%" PRIu8 "\n",
              kind);
      goto fail_rapid_fire;
    }

    for (uint32_t i = 0; i < num_rapid_fire; i++) {
      uint8_t target_kind;
      uint32_t rf;
      n = fscanf(file, "%" SCNu8 "%" SCNu32, &target_kind, &rf);
      if (n != 2) {
        fprintf(stderr,
                "Parsing units attributes failed, cannot scan rapid fire "
                "#%" PRIu8 " for kind #%" PRIu8 "\n",
                i, kind);
        goto fail_rapid_fire;
      }

      if (target_kind >= num_kinds) {
        fprintf(stderr,
                "Parsing units attributes failed, rapid fire #%" PRIu8 " is "
                "invalid for kind #%" PRIu8 "\n",
                i, kind);
        goto fail_rapid_fire;
      }

      attr->rapid_fire[target_kind] = rf;
    }
  }

  return units_attributes;

fail_rapid_fire:
  free(rapid_fire);
fail_units_attributes:
  free(units_attributes);
fail:
  return NULL;
}

static void
cleanup_units_attributes(struct units_attributes *units_attributes) {
  free(units_attributes->rapid_fire);
  free(units_attributes);
}

static struct combatant *
load_combatants(FILE *restrict file,
                const struct units_attributes *restrict units_attributes,
                uint32_t num_combatants) {
  const uint8_t num_kinds = units_attributes->num_kinds;
  int n;

  assert(num_combatants > 0 && num_combatants <= 2 * 256);
  assert(num_kinds > 0);

  size_t total_size =
      num_combatants * sizeof(struct combatant) +
      num_combatants * num_kinds * sizeof(uint64_t) +
      num_combatants * MAX_ROUNDS * num_kinds * sizeof(struct unit_group_stats);
  struct combatant *combatants = calloc(total_size, 1);
  if (combatants == NULL) {
    fputs("Loading combatants failed, allocation of combatants failed\n",
          stderr);
    goto fail;
  }

  uint64_t *unit_groups = (uint64_t *)&combatants[num_combatants];
  struct unit_group_stats *stats =
      (struct unit_group_stats *)&unit_groups[num_combatants * num_kinds];

  for (uint32_t i = 0; i < num_combatants;
       i++, unit_groups += num_kinds, stats += MAX_ROUNDS * num_kinds) {
    struct combatant *c = &combatants[i];
    c->unit_groups = unit_groups;
    c->stats = stats;

    uint8_t num_unit_groups;
    n = fscanf(file, "%" SCNu8 "%" SCNu8 "%" SCNu8 "%" SCNu8,
               &c->weapons_technology, &c->shielding_technology,
               &c->armor_technology, &num_unit_groups);
    if (n != 4) {
      fprintf(stderr,
              "Loading combatants failed, cannot scan combatant #%" PRIu32 "\n",
              i);
      goto fail_combatants;
    }

    for (uint8_t j = 0; j < num_unit_groups; j++) {
      uint8_t kind;
      uint64_t num_units;
      n = fscanf(file, "%" SCNu8 "%" SCNu64, &kind, &num_units);
      if (n != 2) {
        fprintf(stderr,
                "Loading combatants failed, cannot scan unit group #%" PRIu8 " "
                "for combatant #%" PRIu32 "\n",
                j, i);
        goto fail_combatants;
      }

      if (kind >= num_kinds) {
        fprintf(stderr,
                "Loading combatants failed, unit group #%" PRIu8 " is invalid "
                "for combatant #%" PRIu32 "\n",
                j, i);
        goto fail_combatants;
      }

      unit_groups[kind] = num_units;
    }
  }

  return combatants;

fail_combatants:
  free(combatants);
fail:
  return NULL;
}

static struct party *
create_party(const struct units_attributes *restrict units_attributes,
             struct combatant *combatants, uint32_t num_combatants) {
  const uint8_t num_kinds = units_attributes->num_kinds;

  assert(num_combatants <= 256);

  struct party *party = malloc(sizeof(*party));
  if (party == NULL) {
    fputs("Allocating memory for a party failed\n", stderr);
    goto fail;
  }

  uint64_t total_units = 0;
  for (uint32_t i = 0; i < num_combatants; i++) {
    for (uint8_t kind = 0; kind < num_kinds; kind++) {
      uint64_t num_units = combatants[i].unit_groups[kind];

      if (num_units > MAX_UNITS - total_units) {
        fputs("Too many units\n", stderr);
        goto fail_party;
      }

      total_units += num_units;
    }
  }

  assert(total_units <= SIZE_MAX / sizeof(struct unit));
  struct unit *units = malloc(total_units * sizeof(*units));
  if (units == NULL) {
    fputs("Allocating memory for party units failed\n", stderr);
    goto fail_party;
  }

  party->combatants = combatants;
  party->units = units;
  party->num_alive = total_units;

  for (uint32_t i = 0; i < num_combatants; i++) {
    const struct combatant *combatant = &combatants[i];
    for (uint8_t kind = 0; kind < num_kinds; kind++) {
      float max_hull = 0.1f * units_attributes->attributes[kind].armor *
                       (1.0f + 0.1f * combatant->armor_technology);
      for (uint32_t j = 0; j < combatant->unit_groups[kind]; j++) {
        struct unit *unit = units++;
        unit->hull = max_hull;
        unit->kind = kind;
        unit->combatant_id = (uint8_t)i;
      }
    }
  }

  return party;

fail_party:
  free(party);
fail:
  return NULL;
}

static void
restore_shields(const struct units_attributes *restrict units_attributes,
                struct party *restrict party) {
  const struct combatant *combatants = party->combatants;
  struct unit *units = party->units;
  uint64_t num_alive = party->num_alive;

  for (uint64_t i = 0; i < num_alive; i++) {
    struct unit *unit = &units[i];
    unit->shield =
        units_attributes->attributes[unit->kind].shield *
        (1.0f + 0.1f * combatants[unit->combatant_id].shielding_technology);
  }
}

static void fire(const struct units_attributes *restrict units_attributes,
                 struct party *restrict attackers_party,
                 struct party *restrict defenders_party, uint32_t round,
                 uint32_t *restrict random) {
  const uint8_t num_kinds = units_attributes->num_kinds;

  uint32_t r = *random;

  struct combatant *attackers = attackers_party->combatants;
  struct unit *shooters = attackers_party->units;
  uint64_t num_shooters = attackers_party->num_alive;

  struct combatant *defenders = defenders_party->combatants;
  struct unit *targets = defenders_party->units;
  uint64_t num_targets = defenders_party->num_alive;

  for (uint64_t i = 0; i < num_shooters; i++) {
    const struct unit *shooter = &shooters[i];
    uint8_t shooter_kind = shooter->kind;

    const struct unit_attributes *shooter_attrs =
        &units_attributes->attributes[shooter_kind];

    struct combatant *attacker = &attackers[shooter->combatant_id];

    struct unit_group_stats *shooter_stats =
        &attacker->stats[round * num_kinds + shooter_kind];

    float damage =
        shooter_attrs->weapons *
        (1.0f + 0.1f * attackers[shooter->combatant_id].weapons_technology);

    uint32_t rapid_fire;

    do {
      r = RANDOM_NEXT(r);
      struct unit *target = &targets[r % num_targets];
      uint8_t target_kind = target->kind;

      const struct unit_attributes *target_attrs =
          &units_attributes->attributes[target_kind];

      struct combatant *defender = &defenders[target->combatant_id];

      struct unit_group_stats *target_stats =
          &defender->stats[round * num_kinds + target_kind];

      shooter_stats->times_fired++;
      target_stats->times_was_shot++;

      if (target->hull != 0.0f) {
        float hull = target->hull;
        float hull_damage = damage - target->shield;

        if (hull_damage < 0.0f) {
          float max_shield = target_attrs->shield *
                             (1.0f + 0.1f * defender->shielding_technology);
          float shield_damage =
              0.01f * floorf(100.0f * damage / max_shield) * max_shield;
          target->shield -= shield_damage;

          shooter_stats->shield_damage_dealt += (uint64_t)shield_damage;
          target_stats->shield_damage_taken += (uint64_t)shield_damage;
        } else {
          shooter_stats->shield_damage_dealt += (uint64_t)target->shield;
          target_stats->shield_damage_taken += (uint64_t)target->shield;

          target->shield = 0.0f;
          if (hull_damage > hull) {
            hull_damage = hull;
          }
          hull -= hull_damage;

          shooter_stats->hull_damage_dealt += (uint64_t)hull_damage;
          target_stats->hull_damage_taken += (uint64_t)hull_damage;
        }

        if (hull != 0.0f) {
          float max_hull = 0.1f * target_attrs->armor *
                           (1.0f + 0.1f * defender->armor_technology);
          if (hull < 0.7f * max_hull) {
            r = RANDOM_NEXT(r);
            if (hull < (1.0f / (float)RANDOM_MAX) * (float)r * max_hull) {
              hull = 0.0f;
            }
          }
        }
        target->hull = hull;
      }

      rapid_fire = shooter_attrs->rapid_fire[target_kind];
    } while (rapid_fire != 0 && (r = RANDOM_NEXT(r)) % rapid_fire != 0);
  }

  *random = r;
}

static void
update_units(const struct units_attributes *restrict units_attributes,
             struct combatant *restrict combatants,
             struct party *restrict party, uint32_t round) {
  const uint8_t num_kinds = units_attributes->num_kinds;
  struct unit *units = party->units;
  uint64_t num_alive = party->num_alive, n = 0;

  for (uint64_t i = 0; i < num_alive; i++) {
    struct unit *unit = &units[i];
    if (unit->hull != 0.0f) {
      units[n++] = *unit;
      struct combatant *combatant = &combatants[unit->combatant_id];
      combatant->stats[round * num_kinds + unit->kind].num_remaining_units++;
    }
  }

  party->num_alive = n;
}

static void
update_combatants(const struct units_attributes *restrict units_attributes,
                  struct combatant *restrict combatants,
                  uint32_t num_combatants, struct party *restrict party) {
  const uint8_t num_kinds = units_attributes->num_kinds;

  for (uint32_t i = 0; i < num_combatants; i++) {
    struct combatant *combatant = &combatants[i];
    memset(combatant->unit_groups, 0,
           num_kinds * sizeof(*combatant->unit_groups));
  }

  for (uint64_t i = 0; i < party->num_alive; i++) {
    const struct unit *unit = &party->units[i];
    combatants[unit->combatant_id].unit_groups[unit->kind]++;
  }
}

static bool fight(const struct units_attributes *restrict units_attributes,
                  struct combatant *restrict attackers, uint32_t num_attackers,
                  struct combatant *restrict defenders, uint32_t num_defenders,
                  uint32_t *restrict num_rounds, uint32_t *restrict random) {
  bool ret = false;

  struct party *attackers_party =
      create_party(units_attributes, attackers, num_attackers);
  if (attackers_party == NULL) {
    goto out;
  }

  struct party *defenders_party =
      create_party(units_attributes, defenders, num_defenders);
  if (defenders_party == NULL) {
    goto out_attackers_party;
  }

  uint32_t round = 0;

  while (round < MAX_ROUNDS && attackers_party->num_alive > 0 &&
         defenders_party->num_alive > 0) {
    restore_shields(units_attributes, attackers_party);
    restore_shields(units_attributes, defenders_party);

    fire(units_attributes, attackers_party, defenders_party, round, random);
    fire(units_attributes, defenders_party, attackers_party, round, random);

    update_units(units_attributes, attackers, attackers_party, round);
    update_units(units_attributes, defenders, defenders_party, round);

    round++;
  }

  *num_rounds = round;

  update_combatants(units_attributes, attackers, num_attackers,
                    attackers_party);
  update_combatants(units_attributes, defenders, num_defenders,
                    defenders_party);

  ret = true;

  free(defenders_party->units);
  free(defenders_party);
out_attackers_party:
  free(attackers_party->units);
  free(attackers_party);
out:
  return ret;
}

static void dump_stats(FILE *restrict file,
                       const struct combatant *restrict combatants,
                       uint32_t num_combatants, uint32_t num_rounds,
                       uint8_t num_kinds) {
  for (uint32_t i = 0; i < num_combatants; i++) {
    const struct combatant *combatant = &combatants[i];
    for (uint32_t round = 0; round < num_rounds; round++) {
      const struct unit_group_stats *stats =
          &combatant->stats[round * num_kinds];

      for (uint8_t kind = 0; kind < num_kinds; kind++) {
        const struct unit_group_stats *s = &stats[kind];
        fprintf(file,
                "%" PRIu64 " %" PRIu64 " %" PRIu64 " %" PRIu64 " %" PRIu64
                " %" PRIu64 " %" PRIu64 "\n",
                s->times_fired, s->times_was_shot, s->shield_damage_dealt,
                s->hull_damage_dealt, s->shield_damage_taken,
                s->hull_damage_taken, s->num_remaining_units);
      }

      fputc('\n', file);
    }
  }
}

static int battle(uint32_t seed) {
  int n, ret = 1;

  struct units_attributes *units_attributes = load_units_attributes(stdin);
  if (units_attributes == NULL) {
    goto out;
  }

  uint32_t num_attackers, num_defenders;
  n = fscanf(stdin, "%" SCNu32 "%" SCNu32, &num_attackers, &num_defenders);
  if (n != 2) {
    fputs("Scanning the number of combatants failed\n", stderr);
    goto out_units_attributes;
  }

  if (num_attackers == 0 || num_defenders == 0) {
    puts("0");
    ret = 0;
    goto out_units_attributes;
  }

  if (num_attackers > 256) {
    fputs("The number of attackers cannot be greater than 256\n", stderr);
    goto out_units_attributes;
  }

  if (num_defenders > 256) {
    fputs("The number of defenders cannot be greater than 256\n", stderr);
    goto out_units_attributes;
  }

  uint32_t num_combatants = num_attackers + num_defenders;
  struct combatant *combatants =
      load_combatants(stdin, units_attributes, num_combatants);
  if (combatants == NULL) {
    goto out_units_attributes;
  }

  struct combatant *attackers = combatants;
  struct combatant *defenders = &combatants[num_attackers];

  uint32_t num_rounds = 0;
  fight(units_attributes, attackers, num_attackers, defenders, num_defenders,
        &num_rounds, &seed);

  printf("%" PRIu32 "\n\n", num_rounds);

  dump_stats(stdout, combatants, num_attackers + num_defenders, num_rounds,
             units_attributes->num_kinds);

  ret = 0;

  free(combatants);
out_units_attributes:
  cleanup_units_attributes(units_attributes);
out:
  return ret;
}

int main(int argc, char *argv[]) {
  int n;

  if (argc != 2) {
    fprintf(stderr, "Usage: %s [SEED]\n", argv[0]);
    return 1;
  }

  uint32_t seed;
  n = sscanf(argv[1], "%" SCNu32, &seed);
  if (n != 1) {
    fputs("Scanning seed failed\n", stderr);
    return 1;
  }

  if (seed == 0) {
    fputs("Seed cannot be 0\n", stderr);
    return 1;
  }

  return battle(seed);
}

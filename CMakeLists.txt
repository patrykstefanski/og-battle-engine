cmake_minimum_required(VERSION 3.1)
project(BattleEngine C)

option(FAST_MATH "Enable fast math (-ffast-math)" ON)
option(ARCH_NATIVE "Enable optimizations for native arch (-march=native)" OFF)
option(ASAN "Enable Address Sanitizer" OFF)
option(MEMSAN "Enable Memory Sanitizer" OFF)
option(UBSAN "Enable Undefined Behavior Sanitizer" OFF)
option(ANALYZER "Enable static analyzer" OFF)

add_executable(BattleEngine BattleEngine.c)
set_property(TARGET BattleEngine PROPERTY C_STANDARD 99)

if(CMAKE_C_COMPILER_ID MATCHES Clang OR CMAKE_COMPILER_IS_GNUCC)
  target_link_libraries(BattleEngine m)
  target_compile_options(BattleEngine PRIVATE -Wall -Wextra -Wpedantic -Wconversion)

  if(FAST_MATH)
    target_compile_options(BattleEngine PRIVATE -ffast-math)
  endif()

  if(ARCH_NATIVE)
    target_compile_options(BattleEngine PRIVATE -march=native)
  endif()

  if(ASAN)
    target_compile_options(BattleEngine PRIVATE -fsanitize=address)
    set_property(TARGET BattleEngine APPEND_STRING PROPERTY LINK_FLAGS " -fsanitize=address")
  endif()

  if(MEMSAN)
    target_compile_options(BattleEngine PRIVATE -fsanitize=memory)
    set_property(TARGET BattleEngine APPEND_STRING PROPERTY LINK_FLAGS " -fsanitize=memory")
  endif()

  if(UBSAN)
    target_compile_options(BattleEngine PRIVATE -fsanitize=undefined)
    set_property(TARGET BattleEngine APPEND_STRING PROPERTY LINK_FLAGS " -fsanitize=undefined")
  endif()

  if(ANALYZER)
    if(CMAKE_C_COMPILER_ID MATCHES Clang)
      target_compile_options(BattleEngine PRIVATE --analyze)
    else()
      target_compile_options(BattleEngine PRIVATE -fanalyzer)
    endif()
  endif()
endif()

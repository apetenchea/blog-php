cmake_minimum_required(VERSION 3.1)

find_package(LLVM REQUIRED CONFIG)
add_definitions(${LLVM_DEFINITIONS})
include_directories(${LLVM_INCLUDE_DIRS})
link_directories(${LLVM_LIBRARY_DIRS})

# The compiler must be the one used to build Clang.
# Unless otherwise specified, CMake uses the default compiler on your system.
# You can specify any C++ compiler like this:
# set(CMAKE_CXX_COMPILER "g++")

# These logs are not required, but they are useful.
MESSAGE( STATUS "LLVM_DEFINITIONS " ${LLVM_DEFINITIONS} )
MESSAGE( STATUS "LLVM_INCLUDE_DIRS " ${LLVM_INCLUDE_DIRS} )
MESSAGE( STATUS "LLVM_LIBRARY_DIRS " ${LLVM_LIBRARY_DIRS} )
MESSAGE( STATUS "CMAKE_CXX_COMPILER " ${CMAKE_CXX_COMPILER} )

add_subdirectory(multiplication-pass)

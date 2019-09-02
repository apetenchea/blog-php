# This tells CMake to add a library to the project, using the specified source files
add_library(MultiplicationPass MODULE
  MultiplicationPass.cpp
)

# If when you try to use this pass with Clang you get an error like:
#
# undefined symbol: _ZNK4llvm14BasicBlockPass17createPrinterPassERNS_11raw_ostreamERKNSt7__cxx1112basic_stringIcSt11char_traitsIcESaIcEEE'
#
# This might happen because you built the pass using a different
# compiler, other than the one used to build Clang.
# If you can't change that, you might also try to add bellow the following
# compile flag: '-D_GLIBCXX_USE_CXX11_ABI=0'
set_target_properties(MultiplicationPass PROPERTIES
  COMPILE_FLAGS "-fno-rtti -std=c++11"
)

#include <cstdint>

#include "llvm/Pass.h"
#include "llvm/IR/BasicBlock.h"
#include "llvm/IR/LegacyPassManager.h"
#include "llvm/IR/Constants.h"
#include "llvm/IR/IRBuilder.h"
#include "llvm/Transforms/IPO/PassManagerBuilder.h"
#include "llvm/Transforms/Utils/BasicBlockUtils.h"

using namespace llvm;

namespace {
  class MultiplicationPass : public BasicBlockPass {
    public:
      static char ID;
      MultiplicationPass() : BasicBlockPass(ID) {}

      /*
       * This is the entry point of the pass.
       */
      bool runOnBasicBlock(BasicBlock &BB) override {
        /* This will tell whether or not the BasicBlock was modified. */
        bool modified = false;

        /* Iterate through the instructions contained in the given BasicBlock. */
        for (auto it = BB.begin(); it != BB.end(); ++it) {
          Instruction &instruction = *it;

          /*
           * Skip over the current instruction if it's not a multiplication
           * or if the operands are not integers.
           */
          if (instruction.getOpcode() != Instruction::Mul
              || !instruction.getType()->isIntegerTy()) {
            continue;
          }

          /* Check if one of the operands is a constant. */
          int constOperandIdx = 0;
          auto *constInt = dyn_cast<ConstantInt>(instruction.getOperand(0));
          if (!constInt) {
            constInt = dyn_cast<ConstantInt>(instruction.getOperand(1));
            constOperandIdx = 1;
          }
          if (!constInt) {
            continue;
          }

          /* Check if the constant is a power of 2. */
          int32_t exactLog2 = constInt->getValue().exactLogBase2();
          if (exactLog2 == -1) {
            continue;
          }

          /* Create a new left shift instruction. */
          IRBuilder<> builder(&instruction);
          Value *shl = builder.CreateShl(
              instruction.getOperand(!constOperandIdx),
              ConstantInt::get(instruction.getType(), exactLog2));

          /* Replace the current multiplication. */
          ReplaceInstWithValue(BB.getInstList(), it, shl);

          modified = true;
        }

        return modified;
      }
  };
}

char MultiplicationPass::ID = 0;

static void registerMultiplicationPass(const PassManagerBuilder &,
    legacy::PassManagerBase &PM) {
  PM.add(new MultiplicationPass());
}

static RegisterStandardPasses MultiplicationPassRegistration(
    PassManagerBuilder::EP_EarlyAsPossible,
    registerMultiplicationPass);

static RegisterPass<MultiplicationPass> X("mul", "Turns multiplication by powers of 2 into left shifts.",
                             false /* Only looks at CFG */,
                             false /* Analysis Pass */);

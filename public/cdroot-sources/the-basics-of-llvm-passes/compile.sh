#!/bin/sh

clang -std=c11 -Wall -Xclang -load -Xclang build/multiplication-pass/libMultiplicationPass.so -o example example.c

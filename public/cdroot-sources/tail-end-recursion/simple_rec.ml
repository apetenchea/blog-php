(*
 * simple_rec.ml
 * Sums up all integers from 1 to n.
 *)

let rec sum n =
  if n = 1 then 1
  else n + sum (n - 1);;

let n = read_int() in
print_int (sum n);
print_newline();;

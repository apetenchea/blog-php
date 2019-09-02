use std::io;

fn main() {
    let mut x = String::new();
    io::stdin().read_line(&mut x).expect("Error!");
    let x: u32 = x.trim().parse().expect("Error!");
    println!("{}", x * 8);
}

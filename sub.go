package main

import (
	"bufio"
	"fmt"
	"os"
)

func main() {
	var b []byte
	r := bufio.NewReader(os.Stdin)
	_, err := r.Read(b)
	if err != nil {
		return
	}

	fmt.Println(b)
}

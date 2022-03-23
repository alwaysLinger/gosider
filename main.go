package main

import (
	"context"
	"github.com/alwaysLinger/gosider/pkg/hub"
	"os"
)

func main() {
	h := hub.NewHub(os.Stdin, func(ctx context.Context, bytes []byte) ([]byte, error) {
		return []byte{'a', 'b', 'c'}, nil
	})

	h.Start()
}

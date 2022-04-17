<?php

while (1) {
    $b = fread(STDIN, 1024);
    fwrite(STDOUT, $b);
}

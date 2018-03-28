<?php

class GetAndUpdatePriceProduct
{
    public function run()
    {
        echo "Start process at: " . now() . PHP_EOL;
        $get = new GetPriceProductAsia();
        $get->run();
        $update = new UpdatePriceProductAsia();
        $update->run();
        echo "End process at: " . now() . PHP_EOL;
    }
}
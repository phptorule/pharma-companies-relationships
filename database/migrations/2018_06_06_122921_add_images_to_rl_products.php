<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Product;

class AddImagesToRlProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $images = [
            '/product-images/perkinelmer.png',
            '/product-images/roche-cobas.jpg',
            '/product-images/abbott-biomeriux.jpg',
            '/product-images/abbott-architect.jpg',
            '/product-images/alere.jpg',
            '/product-images/roche-urisys.jpg',
            '/product-images/siemens-sysmex.jpg',
            '/product-images/roche.jpg',
            '/product-images/cepheid.png',
            '/product-images/biosite-triage.jpg',
            '/product-images/beckman-coulter-unicel.jpg',
            '/product-images/abbott-m2000.jpg',
            '/product-images/roche-cobas-ampliprep.jpg',
            '/product-images/roche-cobas-taqman.jpg',
            '/product-images/beckman-coulter-unicel.jpg',
            '/product-images/roche-lightcycler.jpg',
            '/product-images/roche-cycler.jpg',
            '/product-images/roche-magna-pure.jpg',
            '/product-images/perkin-elmer-spectrum-ix.png',
            '/product-images/perkin-elmer-spectrum.png',
            '/product-images/biosite.png',
            '/product-images/bio-rad-pastorex.jpg',
            '/product-images/roche.jpg',
            '/product-images/roche.jpg',
            '/product-images/Instrumentation-Laboratory.jpg',
            '/product-images/roche.jpg',
            '/product-images/siemens-clinitek.jpg',
            '/product-images/beckman.jpg',
            '/product-images/abbot-istat.jpg',
            '/product-images/Instrumentation-Laboratory-acustar.jpg',
            '/product-images/siemens-clinitek-status.jpg',
            '/product-images/roche-genexpert.jpg',
            '/product-images/roche-coaguchek.jpg',
            '/product-images/roche-star-evolution.jpg',
            '/product-images/beckman-coulter-navios.jpg',
            '/product-images/siemens.jpg',
            '/product-images/bayer-hematek.jpg',
            '/product-images/beckman.jpg',
            '/product-images/bio-rad.jpg',
            '/product-images/bayer.png',
            '/product-images/abbott.jpg',
            '/product-images/biosite.png',
            '/product-images/beckman-coulter-au.jpg',
        ];

        for ($i = 1; $i <= count($images); $i++) {
            $product = Product::whereId($i)->first();
            $product->image = $images[$i-1];
            $product->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        for ($i = 1; $i <= 43; $i++) {
            $product = Product::whereId($i)->first();
            $product->image = null;
            $product->save();
        }
    }
}

<?php

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetPictureForCompanies extends Seeder
{

    protected $companyPictureArray = [
        '12' => '/product-images/roche-cobas.jpg',
        '42' => '/product-images/abbott-biomeriux.jpg',
        '4' => '/product-images/abbott-architect.jpg',
        '51' => '/product-images/alere.jpg',
        '26' => '/product-images/roche-urisys.jpg',
        '3' => '/product-images/siemens-sysmex.jpg',
        '8' => '/product-images/roche.jpg',
        '36' => '/product-images/roche.jpg',
        '37' => '/product-images/cepheid.png',
        '27' => '/product-images/biosite-triage.jpg',
        '20' => '/product-images/beckman-coulter-unicel.jpg',
        '17' => '/product-images/abbott-m2000.jpg',
        '23' => '/product-images/roche-cobas-ampliprep.jpg',
        '18' => '/product-images/roche-cobas-taqman.jpg',
        '50' => '/product-images/roche-lightcycler.jpg',
        '30' => '/product-images/roche-cycler.jpg',
        '49' => '/product-images/roche-magna-pure.jpg',
        '53' => '/product-images/roche-magna-pure.jpg',
        '57' => '/product-images/perkin-elmer-spectrum.png',
        '14' => '/product-images/biosite.png',
        '19' => '/product-images/bio-rad-pastorex.jpg',
        '11' => '/product-images/Instrumentation-Laboratory.jpg',
        '43' => '/product-images/Instrumentation-Laboratory.jpg',
        '10' => '/product-images/siemens-clinitek.jpg',
        '38' => '/product-images/beckman.jpg',
        '52' => '/product-images/abbot-istat.jpg',
        '48' => '/product-images/Instrumentation-Laboratory-acustar.jpg',
        '29' => '/product-images/siemens-clinitek-status.jpg',
        '34' => '/product-images/roche-coaguchek.jpg',
        '25' => '/product-images/roche-star-evolution.jpg',
        '28' => '/product-images/beckman-coulter-navios.jpg',
        '22' => '/product-images/siemens.jpg',
        '2' => '/product-images/siemens.jpg',
        '9' => '/product-images/siemens.jpg',
        '45' => '/product-images/bayer-hematek.jpg',
        '39' => '/product-images/beckman.jpg',
        '40' => '/product-images/bio-rad.jpg',
        '41' => '/product-images/bayer.png',
        '21' => '/product-images/abbott.jpg'
    ];


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('rl_products')->update(['image' => null]);

        foreach ($this->companyPictureArray as $id => $imagePath) {

            Product::find($id)->update(['image' => $imagePath]);

        }

    }
}

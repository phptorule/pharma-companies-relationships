<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\AddressTag;
use App\Models\AddressProduct;
use App\Models\Cluster;
use App\Models\CustomerType;
use App\Models\People;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Image;
use File;
use Storage;

class AddressesController extends Controller
{


    function index()
    {
        $addresses = $this->prepareAddressesQuery()->get();

        $addressesForResponse = [];

        foreach ($addresses as $i => $address) {
            $addressesForResponse[$i]['id'] = $address['id'];
            $addressesForResponse[$i]['name'] = $address['name'];
            $addressesForResponse[$i]['lat'] = $address['lat'];
            $addressesForResponse[$i]['lon'] = $address['lon'];
            $addressesForResponse[$i]['customer_status'] = $address['customer_status'];
        }

        return response()->json($addressesForResponse);
    }


    function loadAddressesPaginated()
    {
        $query = $this->prepareAddressesQuery();

        $addresses = $query->paginate(20);

        return response()->json($addresses);
    }


    function prepareAddressesQuery()
    {
        $query = Address::with('tags')
            ->with('cluster')
            ->withCount('people')
            ->with(['products' => function($q){
                $q->select('id');
            }]);

        $query = $this->composeConditions($query, request()->all());

        return $query;
    }


    function composeConditions($query, $requestParams)
    {

        if (isset($requestParams['sort-by'])) {

            $field = explode('-',$requestParams['sort-by'])[0];
            $direction = explode('-',$requestParams['sort-by'])[1];

            if($field == 'people') {
                $field .= '_count';
            }
            else if($field == 'products') {
                $query->withCount('products');
                $field .= '_count';
            }

            $query->orderBy($field,$direction);
        }

        if (isset($requestParams['tag-ids'])) {
            $query->whereHas('tags', function ($q) use ($requestParams) {
                $q->whereIn('id', $requestParams['tag-ids']);
            });
        }

        if (isset($requestParams['used-product-ids'])) {
            $query->whereHas('products', function ($q) use ($requestParams) {
                $q->whereIn('id', $requestParams['used-product-ids']);
            });
        }

        if (isset($requestParams['type-id'])) {
            $query->whereHas('customerType', function ($q) use ($requestParams) {
                $q->where('id', $requestParams['type-id']);
            });
        }

        if (isset($requestParams['global-search'])) {
            $query->where('rl_addresses.name', 'LIKE', '%'.$requestParams['global-search'].'%');
        }

        if (isset($requestParams['address-ids'])) {
            $query->whereIn('rl_addresses.id', explode(',',$requestParams['address-ids']));
        }

        return $query;
    }


    function loadFilterValues()
    {
        $tags = Tag::get(['id', 'name']);
        $products = Product::all();
        $customerTypes = CustomerType::visible()->get();

        $filters = [
            'tag_list' => $tags,
            'used_product_list' => $products,
            'customer_types' => $customerTypes
        ];

        return response()->json($filters);
    }


    function show(Address $address)
    {
        $address->load('tags');
        $address->load('cluster');
        $address->load('cluster.addresses');
        $address->load('people');
        $address->load([
            'products' => function ($query) {
                $query->orderByRaw('company, name');
            }
        ]);
        return response()->json($address);
    }


    function updateCustomerStatus(Address $address)
    {
        $address->customer_status = request()->get('status');
        $address->save();

        return response()->json($address);
    }


    function loadPeopleByAddressId (Address $address)
    {
        $people = People::with('addresses')
                        ->whereHas('addresses', function ($q) use ($address){
                            return $q->where('id', $address->id);
                        })
                        ->paginate(10);

        return response()->json($people);
    }


    function getContactsChain(Address $address)
    {

        $mainLabId = $address->id;

        // first get cluster members, and use them for every query.
        $sqlQuery = "SELECT a2.id, a2.name, a2.cluster_id FROM rl_addresses a JOIN rl_addresses a2 WHERE (a.cluster_id = a2.cluster_id OR a2.id = " . $mainLabId . ") AND a.id = " . $mainLabId;
        $cluster_labs = DB::select(DB::raw($sqlQuery));
        $cluster_labs_ids = Address::createRelatedLabsIds($cluster_labs);


        $sql = "SELECT * from
                (SELECT a.id, a.name, a.cluster_id FROM rl_addresses a WHERE a.id IN (" . $cluster_labs_ids . ") 
                UNION
                SELECT a2.id, a2.name, a2.cluster_id FROM rl_addresses a  
                JOIN rl_address_people ap ON a.id = ap.address_id -- workers of main hospital
                JOIN rl_address_connections ac ON ac.from_person_id = ap.person_id -- people who know people on main hospital
                JOIN rl_address_people ap2 ON ap2.person_id = ac.to_person_id -- workplaces of people who know people on main hospital
                JOIN rl_addresses a2 ON ap2.address_id = a2.id 
                WHERE a.id IN (" . $cluster_labs_ids . ") 
                UNION 
                SELECT a2.id, a2.name, a2.cluster_id FROM rl_addresses a  
                JOIN rl_address_people ap ON a.id = ap.address_id -- workers of main hospital
                JOIN rl_address_connections ac ON ac.to_person_id = ap.person_id -- people who know people on main hospital 
                JOIN rl_address_people ap2 ON ap2.person_id = ac.from_person_id -- workplaces of people who know people on main hospital
                JOIN rl_addresses a2 ON ap2.address_id = a2.id 
                WHERE a.id IN(" . $cluster_labs_ids . ")              
                ) related_labs ";


        $related_labs = DB::select(DB::raw($sql));

        $related_labs_ids = "";
        $first = true;
        foreach ($related_labs as $lab){
            if ($first){
                $first = false;
            }else{
                $related_labs_ids = $related_labs_ids . ",";
            }
            $related_labs_ids = $related_labs_ids . $lab ->id;
        }

        $sql = "SELECT rl.id, rl.name, ap.address_id, pt.name as 'workerType' FROM rl_people rl  
                JOIN rl_address_people ap ON ap.person_id = rl.id JOIN rl_people_types pt ON rl.type_id = pt.id  
                WHERE ap.address_id IN (" . $related_labs_ids . ")";

        $lab_workers = DB::select(DB::raw($sql));


        $related_people = [];
        if ($related_labs_ids != ""){
            $sql = "SELECT p.id, p.name, ap.address_id FROM rl_address_people ap JOIN rl_people p ON ap.person_id = p.id  WHERE ap.address_id IN (" . $related_labs_ids . ")";

            $related_people = DB::select(DB::raw($sql));
        }

        // get the relations from related people
        $first = true;
        $related_people_ids = "";
        foreach ($related_people as $p){
            if ($first){
                $first = false;
            }else{
                $related_people_ids = $related_people_ids . ",";
            }
            $related_people_ids = $related_people_ids . $p->id;
        }

        // get relationships and descriptions
        $people_relationships = [];
        if ($related_people_ids != ""){
            $sql = "SELECT ac.from_person_id, ac.to_person_id, ac.edge_weight, act.id as 'connection_type' FROM rl_address_connections ac LEFT JOIN rl_address_connection_types act on ac.edge_type = act.id WHERE ac.from_person_id IN (" . $related_people_ids . ") AND ac.to_person_id IN (" . $related_people_ids . ") ";
            $people_relationships = DB::select(DB::raw($sql));
        }

        $result = [ 'related_labs' => $related_labs, 'related_people' => $related_people, 'relationships' => $people_relationships, 'workers' => $lab_workers ];

        return response()->json($result);
    }


    function getClusterMembersPaginated(Address $address)
    {
        $clusterAddresses = Address::where('cluster_id', $address->cluster_id)->paginate(10);

        return response()->json($clusterAddresses);
    }


    function getClusterStaffPaginated(Address $address) {
        $clusterStaff = People::with('addresses')
            ->whereHas('addresses', function ($q) use ($address) {
                $q->where('cluster_id', $address->cluster_id);
            })
            ->paginate(10);

        return response()->json($clusterStaff);
    }


    function getClusterProductsPaginated(Address $address)
    {
        $products = Product::with('addresses')
            ->whereHas('addresses', function ($q) use ($address) {
                $q->where('cluster_id', $address->cluster_id);
            })
            ->paginate(10);

        return response()->json($products);
    }

    /**
     * update Address
     */
    public function updateAddressDetails(Address $address)
    {
        $address->name = request()->get('name');
        $address->address = request()->get('address');
        $address->url = request()->get('url');
        $address->phone = request()->get('phone');
        $address->save();

        $tags = request()->get('tags');

        $ids = [];

        foreach ($tags as $tag) {
            if ( ! Tag::whereName($tag['name'])->first()) {
                $newTag = new Tag();
                $newTag->name = $tag['name'];
                $newTag->save();
                $ids[] = $newTag->id;
            } else {
                $ids[] = $tag['id'];
            }
        }

        AddressTag::where('address_id', '=', $address->id)->delete();

        foreach ($ids as $tagId) {
            $addressTag = new AddressTag();
            $addressTag->address_id = $address->id;
            $addressTag->tag_id = $tagId;
            $addressTag->save();
        }

        return response()->json($address);
    }

    /**
     * get all tags
     */
    public function loadAllTags(Address $address)
    {
        $tags = Tag::all();

        return response()->json($tags);
    }

    /**
     * get selected tags for address
     */
    public function loadSelectedTags(Address $address)
    {
        $selectedTags = $address->load('tags')->tags;

        return response()->json($selectedTags);
    }

    /**
     * get all clusters
     */
    public function getClusters()
    {
        $clusters = Cluster::get();
        return response()->json($clusters);
    }

    /**
     * update clusters
     */
    public function updateClusters(Address $address)
    {
        $address->cluster_id = request()->get('cluster_id');
        $address->update();
        $address->load('cluster');
        $address->load('cluster.addresses');
        return response()->json($address);
    }

    /**
     * get all products
     */
    public function getProducts()
    {
        $products = Product::orderByRaw('company, name')->get();
        return response()->json($products);
    }

    /**
     * update used products for address
     */
    public function updateProducts(Address $address)
    {
        $selectedProducts = request('selectedProducts');

        AddressProduct::where('address_id', $address->id)->delete();

        if (count($selectedProducts > 0)) {
            foreach ($selectedProducts as $productId) {
                $addressProduct = new AddressProduct();
                $addressProduct->address_id = $address->id;
                $addressProduct->product_id = $productId;
                $addressProduct->save();
            }
        }

        $address->load([
            'products' => function ($query) {
                $query->orderByRaw('company, name');
            }
        ]);

        return response()->json($address);
    }

    /**
     * create new Product
     */
    public function createProduct ()
    {
        $company = trim(request('company'));
        $name = trim(request('name'));
        $description = trim(request('description'));

        if ( ! $company) {
            return response()->json([
                'status' => 'error',
                'message' => "Company field should not be empty"
            ]);
        }

        if ($company && ($name == "" || $name == null)) {
            $prod = Product::whereCompany($company)->where('name', '=', "")->orWhere('name', '=', null)->first();
            if ($prod) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Product with company - '$company' already exists!"
                ]);
            }
        }

        if ($company && $name) {
            $prod = Product::whereCompany($company)->whereName($name)->first();
            if ($prod) {
                return response()->json([
                    'status' => 'error',
                    'message' => "This product already exists!"
                ]);
            }
        }

        if (strlen($company) > 255 || strlen($name) > 255) {
            return response()->json([
                'status' => 'error',
                'message' => "Max count of characters is 255!"
            ]);
        }

        $product = new Product();
        $product->company = $company;
        $product->name = $name;
        $product->description = $description;

        $image = request()->file('image');

        if ($image) {
            $extension = $image->getClientOriginalExtension();

            if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png') {
                $imageName = now()->format('Y-m-d-H-i-s') . '.' . $extension;

                $destinationPath = public_path('product-images');
                
                $result = File::makeDirectory($destinationPath, 0777, true, true);

                File::put(public_path("/product-images/.gitignore"), "*\r\n!.gitignore\r\n");

                $img = Image::make($image->getRealPath());

                $img->resize(100, 100, function ($constraint) {

                    $constraint->aspectRatio();
                
                })->save(public_path("/product-images/$imageName"));

                $product->image = "/product-images/$imageName";
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => "Only jpg/jpeg/png files are allowed!"
                ]);
            }
        }
        
        $product->save();

        $products = Product::orderByRaw('company, name')->get();

        return response()->json($products);
    }

    /**
     * get all people for lab chain
     */
    public function getAllClusterStaff (Address $address)
    {
        $clusterStaff = People::with('addresses')
            ->whereHas('addresses', function ($q) use ($address) {
                $q->where('cluster_id', $address->cluster_id);
            })
            ->get();

        return response()->json($clusterStaff);
    }
}

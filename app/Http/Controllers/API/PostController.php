<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use App\Models\Website;
use Illuminate\Http\Request;
use App\Helper\RequestHelpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    private $helper;
    public function __construct(RequestHelpers $helper)
    {
        $this->helper = $helper;

    }

     // Validation helper functions
     public function validatePostFields()
     {
         return [
             'title' => 'required',
             'body' => 'required',
             'website_code' => 'required'
         ];
     }

     // Prepare db dump data
     public function submitPostData($data)
     {
         return [
             'title' => $data['title'],
             'body' => $data['body'],
             'website_id' => $data['website_code']
         ];
     }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::latest()->paginate(20);

        return $this->helper->successResponse($posts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       $data = $request->only(['title', 'body', 'website_code']);

        // Validate form fields
        $validate = Validator::make($data, $this->validatePostFields());

        if ($validate->fails()){
            return $this->helper->failResponse($validate->errors()->first());
        }

        // Check if selected website exists
        $validate_website = Website::query()->where('code', $data['website_code'])->first();

        $validate_website = Website::query()->where('code', $data['website_code'])->first();


        if (!empty($validate_website)){

            $data['website_code'] = $validate_website->id;

            DB::beginTransaction();

            try {

                Post::query()->create($this->submitPostData($data));

                DB::commit();

                return $this->helper->successResponse("Post created successfully");

            } catch (\Exception $e){

                DB::rollback();

                Log::error($e->getMessage().' Line: '.$e->getLine());

                return $this->helper->failResponse("Post could not be created. Kindly try again");

            }
        }

        return $this->helper->failResponse('The selected website does not exist');


    }


}

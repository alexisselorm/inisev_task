<?php

namespace App\Http\Controllers\API;

use App\Helper\RequestHelpers;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'website_code' => 'required',
            'post_code' => 'required',
        ];
    }

    // Prepare db dump data
    public function submitPostData($data)
    {
        return [
            'title' => $data['title'],
            'body' => $data['body'],
            'website_id' => $data['website_code'],
            'post_code' => $data['post_code'],
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
        $data = $request->only(['title', 'body', 'website_code', 'post_code']);

        // Validate form fields
        $validate = Validator::make($data, $this->validatePostFields());

        if ($validate->fails()) {
            return $this->helper->failResponse($validate->errors()->first());
        }
        // Check if post already exists based on post_code
        $post_exists = Post::where('post_code', $data['post_code'])->exists();
        // Check if selected website exists
        $validate_website = Website::query()->where('code', $data['website_code'])->first();

        $validate_website = Website::query()->where('code', $data['website_code'])->first();

        if (!empty($validate_website) && !$post_exists) {

            $data['website_code'] = $validate_website->id;

            DB::beginTransaction();

            try {

                Post::query()->create($this->submitPostData($data));

                DB::commit();

                return $this->helper->successResponse("Post created successfully");

            } catch (\Exception$e) {

                DB::rollback();

                Log::error($e->getMessage() . ' Line: ' . $e->getLine());

                return $this->helper->failResponse("Post could not be created. Kindly try again");

            }
        }

        return $this->helper->failResponse('The selected website does not exist or Post code has already been chosen');

    }

}

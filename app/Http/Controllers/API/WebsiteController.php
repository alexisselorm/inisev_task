<?php

namespace App\Http\Controllers\API;

use App\Helper\RequestHelpers;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WebsiteController extends Controller
{
    private $helper;
    public function __construct(RequestHelpers $helper)
    {
        $this->helper = $helper;

    }

    // Validation helper functions
    public function validSubscription($user, $website)
    {
        $user = User::query()->where('code', $user)->first();

        $website = Website::query()->where('code', $website)->first();

        if (!empty($user) && !empty($website)) {

            $subscription_exists = Subscription::query()->where('user_id', $user->id)
                ->where('website_id', $website->id)->exists();
            // dd($subscription_exists);

            if (!$subscription_exists) {
                return [
                    'status' => true,
                    'user' => $user,
                    'website' => $website,
                ];
            }

        }

        return [
            'status' => false,
        ];
    }

    public function checkValidationFields()
    {
        return [
            'name' => 'required|string',
            'url' => 'nullable|url',
            'code' => 'required|unique:websites,code',
        ];
    }

    public function submitWebsiteData($data)
    {
        return [
            'name' => $data['name'],
            'code' => $data['code'],
            'url' => $data['url'],
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $websites = Website::latest()->paginate(20);

        return $this->helper->successResponse($websites);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->only(['name', 'url', 'code']);

        // Validate website
        $validate = Validator::make($data, $this->checkValidationFields());

        if ($validate->fails()) {
            return $this->helper->failResponse($validate->errors()->first());
        }

        DB::beginTransaction();

        try {

            $website = Website::query()->create($this->submitWebsiteData($data));

            DB::commit();

            return $this->helper->successResponse("$website->name created successfully");

        } catch (\Exception$exception) {

            DB::rollback();

            Log::error($exception->getMessage() . ' on Line: ' . $exception->getLine());

            return $this->helper->failResponse("Website could not be created. Kindly try again");

        }

    }

    //Subscribe to a website

    public function subscribe(Request $request)
    {
        $data = $request->only(['website_code', 'user_code']);

        $validate = Validator::make($data, [
            'user_code' => 'required',
            'website_code' => 'required',
        ]);

        if ($validate->fails()) {
            return $this->helper->failResponse($validate->errors()->first());
        }

        // Check if user_code and website_code supplied are valid

        $is_valid = $this->validSubscription($data['user_code'], $data['website_code']);

        if ($is_valid['status'] == true) {

            DB::beginTransaction();

            try {

                Subscription::query()->create([
                    'user_id' => $is_valid['user']->id,
                    'website_id' => $is_valid['website']->id,
                ]);

                DB::commit();

                return $this->helper->successResponse($is_valid['user']->name . ' has successfully subscribed to ' . $is_valid['website']->name . ' successfully');

            } catch (\Exception$exception) {

                DB::rollback();

                Log::error($exception->getMessage() . ' on Line: ' . $exception->getLine());

                return $this->helper->failResponse("Subscription was unsuccessful");

            }

        } else {
            return $this->helper->failResponse('The provided webiste code does not exist or the user has already subscribed to the website');
        }
    }

}

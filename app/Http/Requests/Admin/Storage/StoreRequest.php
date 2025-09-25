<?php

namespace App\Http\Requests\Admin\Storage;

use App\Http\Requests\CoreRequest;

class StoreRequest extends CoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        $rules = [];

        switch ($this->storage) {
            case 'aws_s3':
                $rules = [
                    'aws_key'    => 'required|min:10|max:50',
                    'aws_region' => 'required',
                    'aws_secret' => 'required|min:30|max:60',
                    'aws_bucket' => 'required',
                ];
                break;

            case 'digitalocean':
                $rules = [
                    'digitalocean_key'    => 'required|min:3|max:50',
                    'digitalocean_region' => 'required',
                    'digitalocean_secret' => 'required|min:10|max:80',
                    'digitalocean_bucket' => 'required',
                ];
                break;

            case 'wasabi':
                $rules = [
                    'wasabi_key'    => 'required|min:3|max:50',
                    'wasabi_region' => 'required',
                    'wasabi_secret' => 'required|min:10|max:80',
                    'wasabi_bucket' => 'required',
                ];
                break;

            case 'minio':
                $rules = [
                    'minio_key'    => 'required|min:3|max:50',
                    'minio_region' => 'required',
                    'minio_secret' => 'required|min:10|max:80',
                    'minio_bucket' => 'required',
                ];
                break;
        }

        return $rules;
    }
}

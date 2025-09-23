<?php

namespace App\Models\SuperAdmin;

use App\Models\BaseModel;

class GlobalInvoiceSetting extends BaseModel
{
    // Append these computed attributes to the model's JSON representation
    protected $appends = ['logo_url', 'authorised_signatory_signature_url', 'is_chinese_lang'];

    /**
     * Accessor: Returns the URL for the logo
     * - If no logo is set, return the global default logo URL
     * - Otherwise, return the S3/local asset URL
     */
    public function getLogoUrlAttribute()
    {
        return (is_null($this->logo))
            ? global_setting()->logo_url
            : asset_url_local_s3('app-logo/' . $this->logo);
    }

    /**
     * Accessor: Returns the URL for the authorised signatory signature
     * - If no signature is set, returns an empty string
     * - Otherwise, returns the S3/local asset URL
     */
    public function getAuthorisedSignatorySignatureUrlAttribute()
    {
        return (is_null($this->authorised_signatory_signature))
            ? ''
            : asset_url_local_s3('app-logo/' . $this->authorised_signatory_signature);
    }

    /**
     * Accessor: Checks if the current locale represents a Chinese language
     * - Returns true if locale is one of: zh-hk, zh-cn, zh-sg, zh-tw, cn
     * - Returns false otherwise
     */
    public function getIsChineseLangAttribute()
    {
        return in_array(strtolower($this->locale), ['zh-hk', 'zh-cn', 'zh-sg', 'zh-tw', 'cn']);
    }
}

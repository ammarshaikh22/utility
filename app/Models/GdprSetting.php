<?php

namespace App\Models;

/**
 * App\Models\GdprSetting
 *
 * Stores GDPR-related settings for the application, controlling visibility,
 * customer/lead consent, data removal, and terms/policy content.
 *
 * @property int $id                                Primary key
 * @property int $enable_gdpr                       Enable/disable GDPR compliance
 * @property int $show_customer_area                Show GDPR info in customer area
 * @property int $show_customer_footer              Show GDPR info in customer footer
 * @property string|null $top_information_block     Text block displayed at top for GDPR
 * @property int $enable_export                     Allow users to export their data
 * @property int $data_removal                      Enable data removal requests
 * @property int $lead_removal_public_form          Allow public lead removal form
 * @property int $terms_customer_footer             Show terms in customer footer
 * @property string|null $terms                     Terms & conditions text
 * @property string|null $policy                    Privacy policy text
 * @property int $public_lead_edit                  Allow leads to edit data via public form
 * @property int $consent_customer                  Require customer consent
 * @property int $consent_leads                     Require lead consent
 * @property string|null $consent_block             Text block displayed for consent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon                       Associated icon (accessor)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereConsentBlock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereConsentCustomer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereConsentLeads($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereDataRemoval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereEnableExport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereEnableGdpr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereLeadRemovalPublicForm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting wherePolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting wherePublicLeadEdit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereShowCustomerArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereShowCustomerFooter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereTermsCustomerFooter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereTopInformationBlock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprSetting whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class GdprSetting extends BaseModel
{
    // Protects the ID from mass assignment
    protected $guarded = ['id'];
}

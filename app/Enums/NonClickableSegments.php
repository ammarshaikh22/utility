<?php

namespace App\Enums;

enum NonClickableSegments: string
{

    // phpcs:disable
    // Represents a segment for credits that have been applied to an account or invoice.
    case APPLIED_CREDITS = 'applied-credits';
    // Represents a segment for invoices that have been credited.
    case CREDIT_INVOICES = 'credited-invoices';
    // Represents a segment for viewing transaction details.
    case VIEW_TRANSACTION = 'view-transaction';
    // Represents a segment for applying credits to a specific invoice.
    case APPLY_TO_INVOICE = 'apply-to-invoice';
    // Represents a segment for converting an invoice to another form or status.
    case CONVERT_INVOICE = 'convert-invoice';
    // Represents a segment for notes related to a project.
    case PROJECT_NOTES = 'project-notes';
    // Represents a segment for client contact information.
    case CLIENT_CONTACT = 'client-contacts';
    // phpcs:enable

    // Retrieves all enum values as an array by mapping each case to its string value.
    public static function getValues()
    {
        return array_map(function($enum) {
            return $enum->value;
        }, self::cases());
    }

}
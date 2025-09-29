<?php

namespace App\Traits;

use App\Models\UniversalSearch;

/**
 * Trait UniversalSearchTrait
 *
 * Provides functionality to log searchable entries
 * into the `universal_searches` table. This allows
 * items across different modules to be indexed and
 * retrieved through a universal search system.
 */
trait UniversalSearchTrait
{
    /**
     * Logs a new entry into the universal search table.
     *
     * @param  int         $searchableId  The ID of the entity being indexed (e.g., project, task, user).
     * @param  string      $title         A human-readable title to display in search results.
     * @param  string      $route         The route name or URL that links to the entity.
     * @param  string      $type          The type or module of the entity (e.g., "project", "ticket").
     * @param  int|null    $company_id    The company ID (optional, useful in multi-tenant apps).
     * 
     * @return void
     *
     * @throws RelatedResourceNotFoundException
     */
    public function logSearchEntry($searchableId, $title, $route, $type, $company_id = null)
    {
        // Create a new universal search record
        $search = new UniversalSearch();

        // Assign core attributes
        $search->company_id = $company_id;
        $search->searchable_id = $searchableId;
        $search->title = $title;
        $search->route_name = $route;
        $search->module_type = $type;

        // Save record into the database
        $search->save();
    }
}

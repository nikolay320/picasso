<?php
interface Sabai_Addon_Taxonomy_ITaxonomies
{
    public function taxonomyGetTaxonomyNames();
    public function taxonomyGetTaxonomy($name);
}
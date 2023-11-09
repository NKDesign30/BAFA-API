<?php
class Job_Importer_CPT
{

  public function register_cpt()
  {
    $labels = array(
      'name'                  => _x('Job Listings', 'Post Type General Name', 'job-importer'),
      'singular_name'         => _x('Job Listing', 'Post Type Singular Name', 'job-importer'),
      'menu_name'             => __('Job Listings', 'job-importer'),
      'name_admin_bar'        => __('Job Listing', 'job-importer'),
      'archives'              => __('Job Archives', 'job-importer'),
      'attributes'            => __('Job Attributes', 'job-importer'),
      'parent_item_colon'     => __('Parent Job:', 'job-importer'),
      'all_items'             => __('All Jobs', 'job-importer'),
      'add_new_item'          => __('Add New Job', 'job-importer'),
      'add_new'               => __('Add New', 'job-importer'),
      'new_item'              => __('New Job', 'job-importer'),
      'edit_item'             => __('Edit Job', 'job-importer'),
      'update_item'           => __('Update Job', 'job-importer'),
      'view_item'             => __('View Job', 'job-importer'),
      'view_items'            => __('View Jobs', 'job-importer'),
      'search_items'          => __('Search Job', 'job-importer'),
      'not_found'             => __('Not found', 'job-importer'),
      'not_found_in_trash'    => __('Not found in Trash', 'job-importer'),
      'featured_image'        => __('Featured Image', 'job-importer'),
      'set_featured_image'    => __('Set featured image', 'job-importer'),
      'remove_featured_image' => __('Remove featured image', 'job-importer'),
      'use_featured_image'    => __('Use as featured image', 'job-importer'),
      'insert_into_item'      => __('Insert into job', 'job-importer'),
      'uploaded_to_this_item' => __('Uploaded to this job', 'job-importer'),
      'items_list'            => __('Jobs list', 'job-importer'),
      'items_list_navigation' => __('Jobs list navigation', 'job-importer'),
      'filter_items_list'     => __('Filter jobs list', 'job-importer'),
    );
    $args = array(
      'label'                 => __('Job Listing', 'job-importer'),
      'description'           => __('Job listings for the job importer plugin', 'job-importer'),
      'labels'                => $labels,
      'supports'              => array('title', 'editor', 'thumbnail'),
      'taxonomies'            => array('category', 'post_tag'),
      'hierarchical'          => false,
      'public'                => true,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'menu_position'         => 5,
      'show_in_admin_bar'     => true,
      'show_in_nav_menus'     => true,
      'can_export'            => true,
      'has_archive'           => true,
      'exclude_from_search'   => false,
      'publicly_queryable'    => true,
      'capability_type'       => 'post',
      'show_in_rest'          => true,
    );
    register_post_type('job_listing', $args);
  }
}

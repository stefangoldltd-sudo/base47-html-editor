<?php
/**
 * Base47 GitHub Updater
 * Light, safe, automatic update checker for GitHub releases.
 */

class Base47_GitHub_Updater {

    private $plugin_file;
    private $github_repo;
    private $plugin_version;

    public function __construct( $plugin_file, $github_repo, $plugin_version ) {
        $this->plugin_file    = $plugin_file;
        $this->github_repo    = $github_repo; // "user/repo"
        $this->plugin_version = $plugin_version;

        add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_for_update' ] );
        add_filter( 'plugins_api', [ $this, 'plugins_api' ], 10, 3 );
    }

    /** Check GitHub for a new version */
    public function check_for_update( $transient ) {

        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $api_url = "https://api.github.com/repos/{$this->github_repo}/releases/latest";
        $response = wp_remote_get( $api_url, [
            'headers' => [ 'User-Agent' => 'WordPress Update Checker' ],
        ] );

        if ( is_wp_error( $response ) ) {
            return $transient;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ) );

        if ( empty( $data->tag_name ) ) {
            return $transient;
        }

        $latest_version = ltrim( $data->tag_name, 'v' );

        if ( version_compare( $this->plugin_version, $latest_version, '>=' ) ) {
            return $transient; // No update
        }

        $plugin_slug = plugin_basename( $this->plugin_file );

        $transient->response[ $plugin_slug ] = (object) [
            'slug'        => $plugin_slug,
            'new_version' => $latest_version,
            'package'     => $data->zipball_url,
            'url'         => "https://github.com/{$this->github_repo}",
            'tested'      => get_bloginfo( 'version' ),
        ];

        return $transient;
    }

    /** Show plugin details modal */
    public function plugins_api( $result, $action, $args ) {

        $plugin_slug = plugin_basename( $this->plugin_file );

        if ( 'plugin_information' !== $action || $args->slug !== $plugin_slug ) {
            return $result;
        }

        $api_url = "https://api.github.com/repos/{$this->github_repo}/releases/latest";

        $response = wp_remote_get( $api_url, [
            'headers' => [ 'User-Agent' => 'WordPress Update Checker' ],
        ] );

        if ( is_wp_error( $response ) ) {
            return $result;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ) );

        if ( empty( $data->tag_name ) ) {
            return $result;
        }

        $obj = new stdClass();
        $obj->name = 'Base47 HTML Editor';
        $obj->version = $data->tag_name;
        $obj->sections = [
            'description' => 'Automatic update from GitHub. Latest release info from repository.',
        ];
        $obj->download_link = $data->zipball_url;

        return $obj;
    }

}
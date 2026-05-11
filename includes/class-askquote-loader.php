<?php
/**
 * Register all actions and filters for the plugin.
 *
 * @package AskQuote
 */

defined( 'ABSPATH' ) || exit;

/**
 * Maintains lists of all hooks registered by the plugin and runs them.
 */
class Askquote_Loader {

	/**
	 * Registered actions.
	 *
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Registered filters.
	 *
	 * @var array
	 */
	protected $filters = array();

	/**
	 * Add an action hook.
	 *
	 * @param string   $hook          The name of the WordPress action.
	 * @param object   $component     A reference to the instance of the class.
	 * @param string   $callback      The name of the method on the component.
	 * @param int      $priority      Optional. Priority. Default 10.
	 * @param int      $accepted_args Optional. Accepted args count. Default 1.
	 * @return void
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a filter hook.
	 *
	 * @param string   $hook          The name of the WordPress filter.
	 * @param object   $component     A reference to the instance of the class.
	 * @param string   $callback      The name of the method on the component.
	 * @param int      $priority      Optional. Priority. Default 10.
	 * @param int      $accepted_args Optional. Accepted args count. Default 1.
	 * @return void
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a hook to the internal collection.
	 *
	 * @param array  $hooks         Existing hooks.
	 * @param string $hook          Hook name.
	 * @param object $component     Component instance.
	 * @param string $callback      Method name.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Accepted args count.
	 * @return array
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
		return $hooks;
	}

	/**
	 * Register all collected actions and filters with WordPress.
	 *
	 * @return void
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}
}

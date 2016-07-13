<?php

namespace Core\Interfaces;

/**
 * Objects that implement Task heir object lifecycle is managed by the Core_Daemon application object.
 * They are instantiated early, before workers are created and before the event loop starts.
 *
 * All plugins are passed a reference to the Core_Daemon application object when they are instantiated.
 */
interface Task {
    /**
     * Called on Construct or Init
     * @return void
     */
    public function setup();

    /**
     * Called on Destruct
     * @return void
     */
    public function teardown();

    /**
     * This is called after setup() returns
     * @return void
     */
    public function start();

    /**
     * Give your ITask object a group name so the ProcessManager can identify and group processes. Or return Null
     * to just use the current __class__ name.
     * @return string
     */
    public function group();
}
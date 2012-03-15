<?php

	/**
	 * Class for SQL table interface.
	 *
	 * @author Mathieu Desvé, <mathieu.desve@unflux.fr>
	 * @package Table
	 */

	/* user defined includes */

	/* user defined constants */

	/**
	 * Class for SQL table interface.
	 *
	 * @access public
	 * @author Mathieu Desvé, <mathieu.desve@unflux.fr>
	 * @package Table
	 */
	class Medias extends Zend_Db_Table_Abstract
	{
	    // --- ASSOCIATIONS ---


	    // --- ATTRIBUTES ---

	    /**
	     * Table Name
	     *
	     * @access protected
	     * @var string
	     */
	    protected $_name = 'medias';

	    /**
	     * The primary key column or columns.
	     * A compound key should be declared as an array.
	     * You may declare a single-column primary key
	     * as a string.
	     *
	     * @access protected
	     * @var mixed
	     */
	    protected $_primary = 'id';

	    // --- OPERATIONS ---

	} /* end of class Table_Comments */
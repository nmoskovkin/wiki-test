<?php

namespace Acme\WikiBundle\Model\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'pages' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    propel.generator.src.Acme.WikiBundle.Model.map
 */
class PagesTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'src.Acme.WikiBundle.Model.map.PagesTableMap';

    /**
     * Initialize the table attributes, columns and validators
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('pages');
        $this->setPhpName('Pages');
        $this->setClassname('Acme\\WikiBundle\\Model\\Pages');
        $this->setPackage('src.Acme.WikiBundle.Model');
        $this->setUseIdGenerator(false);
        // columns
        $this->addPrimaryKey('id', 'Id', 'VARCHAR', true, 255, null);
        $this->getColumn('id', false)->setPrimaryString(true);
        $this->addColumn('header', 'Header', 'VARCHAR', true, 255, null);
        $this->getColumn('header', false)->setPrimaryString(true);
        $this->addColumn('body', 'Body', 'LONGVARCHAR', false, null, null);
        $this->addColumn('parent', 'Parent', 'VARCHAR', false, 255, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
    } // buildRelations()

} // PagesTableMap

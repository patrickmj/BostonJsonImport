<?php

class BostonJsonImport_ImportController extends Omeka_Controller_AbstractActionController
{
    
    public function importAction()
    {
        $jsonFilePath = PLUGIN_DIR . "/BostonJsonImport/files/bostonJsonTest.json";
        $json = json_decode(file_get_contents($jsonFilePath), true);
        $ids = array();
        foreach($json as $data) {
            $itemMetadata = array('item_type_id' => 1); //change this for your item type id
            $itemElementsData = array('Dublin Core' => array(
                                            'Description' => array(array('text'=>'This story collected ....', 'html'=>0)),
                                               ),
                                       'Item Type Metadata' => array(
                                              'Text' => array(array('text' => urldecode($data['body']), 'html'=>0)) //change Text to the name of your element
                                               ) 
                                      );
            $item = insert_item($itemMetadata, $itemElementsData);
            $location = new Location;
            $location->item_id = $item->id;
            $location->latitude = $data['lat'];
            $location->longitude = $data['lng'];
            $location->zoom_level = 9; // change this?
            $location->map_type = "Google Maps v3.x";
            $location->address = '';
            $location->save();
            $ids[] = $item->id;
            
        }
        echo "<p>Imported: " . count($ids) . "</p>";
        $lastImportFilePath = PLUGIN_DIR . "/BostonJsonImport/files/last_import.txt";
        file_put_contents($lastImportFilePath, serialize($ids));
    }
    
    public function undoAction()
    {
        $table = $this->_helper->db->getTable('Item');
        $lastImportFilePath = PLUGIN_DIR . "/BostonJsonImport/files/last_import.txt";
        $itemIds = unserialize(file_get_contents($lastImportFilePath));
        print_r($itemIds);
        
        foreach($itemIds as $id) {
            
            $item = $table->find($id);
            if($item) {
                $item->delete();
            }
            
        }
    }
}
<?php
namespace Album\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;

/*
There's a lot going on here. Firstly, we set the protected property $tableGateway to the TableGateway instance passed in the constructor, hinting against the TableGatewayInterface (which allows us to provide alternate implementations easily, including mock instances during testing). We will use this to perform operations on the database table for our albums.

We then create some helper methods that our application will use to interface with the table gateway. fetchAll() retrieves all albums rows from the database as a ResultSet, getAlbum() retrieves a single row as an Album object, saveAlbum() either creates a new row in the database or updates a row that already exists, and deleteAlbum() removes the row completely. The code for each of these methods is, hopefully, self-explanatory.
*/
class AlbumTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        return $this->tableGateway->select();
    }

    public function getAlbum($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(['id' => $id]);
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifier %d',
                $id
            ));
        }

        return $row;
    }

    public function saveAlbum(Album $album)
    {
        $data = [
            'artist' => $album->artist,
            'title'  => $album->title,
        ];

        $id = (int) $album->id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            return;
        }

        if (! $this->getAlbum($id)) {
            throw new RuntimeException(sprintf(
                'Cannot update album with identifier %d; does not exist',
                $id
            ));
        }

        $this->tableGateway->update($data, ['id' => $id]);
    }

    public function deleteAlbum($id)
    {
        $this->tableGateway->delete(['id' => (int) $id]);
    }
}

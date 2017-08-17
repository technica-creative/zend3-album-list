<?php
namespace Album\Controller;

use Album\Model\AlbumTable;
use Album\Form\AlbumForm;
use Album\Model\Album;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AlbumController extends AbstractActionController
{
  /*
  Our controller now depends on AlbumTable, so we will need to create a factory for the controller. Similar to how we created factories for the model, we'll create it in our Module class, only this time, under a new method, Album\Module::getControllerConfig()
  */
  private $table;

  public function __construct(AlbumTable $table)
  {
    $this->table = $table;
  }
  /*
  Each action is a public method within the controller class that is named {action name}Action, where {action name} should start with a lower case letter.
  We have now set up the four actions that we want to use. They won't work yet until we set up the views. The URLs for each action are:
  localhost:8080/album
  localhost:8080/album/add
  localhost:8080/album/edit
  localhost:8080/album/delete
  We now have a working router (in module.config.php inside the Album/config folder) and the actions are set up for each page of our application.
  */

  /*
  With Zend Framework, in order to set variables in the view, we return a ViewModel instance where the first parameter of the constructor is an array containing data we wish to represent. These are then automatically passed to the view script. The ViewModel object also allows us to change the view script that is used, but the default is to use {module name}/{controller name}/{action name}.
  We can now fill in the index.phtml view script
  */
  public function indexAction()
  {
    return new ViewModel([
      'albums' => $this->table->fetchAll(),
    ]);
  }

  public function addAction()
  {
    /*
    We instantiate AlbumForm and set the label on the submit button to "Add". We do this here as we'll want to re-use the form when editing an album and will use a different label.
    */
    $form = new AlbumForm();
    $form->get('submit')->setValue('Add');

    /*
    If the request is not a POST request, then no form data has been submitted, and we need to display the form. zend-mvc allows you to return an array of data instead of a view model if desired; if you do, the array will be used to create a view model.
    */
    $request = $this->getRequest();

    if (! $request->isPost()) {
        return ['form' => $form];
    }

    /*
    At this point, we know we have a form submission. We create an Album instance, and pass its input filter on to the form; additionally, we pass the submitted data from the request instance to the form.
    */
    $album = new Album();
    $form->setInputFilter($album->getInputFilter());
    $form->setData($request->getPost());

    /*
    If form validation fails, we want to redisplay the form. At this point, the form contains information about what fields failed validation, and why, and this information will be communicated to the view layer.
    */
    if (! $form->isValid()) {
        return ['form' => $form];
    }

    $album->exchangeArray($form->getData());
    $this->table->saveAlbum($album);

    /*
    After we have saved the new album row, we redirect back to the list of albums using the Redirect controller plugin.
    */
    return $this->redirect()->toRoute('album');
  }

  public function editAction()
  {
    // Firstly, we look for the id that is in the matched route and use it to load the album to be edited:
    // params is a controller plugin that provides a convenient way to retrieve parameters from the matched route. We use it to retrieve the id from the route we created within the Album module's module.config.php. (constraints/id)
    $id = (int) $this->params()->fromRoute('id', 0);

    if (0 === $id) {
        return $this->redirect()->toRoute('album', ['action' => 'add']);
    }

    // Retrieve the album with the specified id entity from the database.. Doing so raises an exception if the album is not found, which should result in redirecting to the landing page.
    try {
        $album = $this->table->getAlbum($id);
    } catch (\Exception $e) {
        return $this->redirect()->toRoute('album', ['action' => 'index']);
    }

    /*
    The form's bind() method attaches the model to the form. This is used in two ways:

    1. When displaying the form, the initial values for each element are extracted from the model.
    2. After successful validation in isValid(), the data from the form is put back into the model.

    These operations are done using a hydrator object. There are a number of hydrators, but the default one is Zend\Hydrator\ArraySerializable which expects to find two methods in the model: getArrayCopy() and exchangeArray().

    We have already written exchangeArray() in our Album entity, so we now need to write getArrayCopy().

    As a result of using bind() with its hydrator, we do not need to populate the form's data back into the $album as that's already been done, so we can just call the mapper's saveAlbum() method to store the changes back to the database.
    */
    $form = new AlbumForm();
    $form->bind($album);
    $form->get('submit')->setAttribute('value', 'Edit');

    $request = $this->getRequest();
    $viewData = ['id' => $id, 'form' => $form];

    if (! $request->isPost()) {
        return $viewData;
    }

    $form->setInputFilter($album->getInputFilter());
    $form->setData($request->getPost());

    if (! $form->isValid()) {
        return $viewData;
    }

    // #2 from comments above
    $this->table->saveAlbum($album);

    // Redirect to album list
    return $this->redirect()->toRoute('album', ['action', 'index']);

  }

  public function deleteAction()
  {
     $id = (int) $this->params()->fromRoute('id', 0);

     if (!$id) {
         return $this->redirect()->toRoute('album');
     }

     $request = $this->getRequest();

     // check the request object's isPost() to determine whether to show the confirmation page or to delete the album
     if ($request->isPost()) {
         $del = $request->getPost('del', 'No');

         if ($del == 'Yes') {
             $id = (int) $request->getPost('id');

             // We use the table object to delete the row using the deleteAlbum() method and then redirect back the list of albums.
             $this->table->deleteAlbum($id);
         }

         // Redirect to list of albums
         return $this->redirect()->toRoute('album');
     }

     // If the request is not a POST, then we retrieve the correct database record and assign to the view, along with the id.
     return [
         'id'    => $id,
         'album' => $this->table->getAlbum($id),
     ];
    }
}

<?php
namespace Album\Model;

use DomainException;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\StringLength;

class Album implements InputFilterAwareInterface
{
  public $id;
  public $artist;
  public $title;

  private $inputFilter;

  /*
  Our Album entity object is a PHP class. In order to work with zend-db's TableGateway class, we need to implement the exchangeArray() method; this method copies the data from the provided array to our entity's properties. We will add an input filter later to ensure the values injected are valid.
  */
  public function exchangeArray(array $data)
  {
    $this->id     = !empty($data['id']) ? $data['id'] : null;
    $this->artist = !empty($data['artist']) ? $data['artist'] : null;
    $this->title  = !empty($data['title']) ? $data['title'] : null;
  }

  /*
  These operations are done using a hydrator object. There are a number of hydrators, but the default one is Zend\Hydrator\ArraySerializable which expects to find two methods in the model: getArrayCopy() and exchangeArray().
  *Used in AlbumController.php editAction()
  */
  public function getArrayCopy()
    {
        return [
            'id'     => $this->id,
            'artist' => $this->artist,
            'title'  => $this->title,
        ];
    }

  /*
  The InputFilterAwareInterface defines two methods: setInputFilter() and getInputFilter(). We only need to implement getInputFilter() so we throw an exception from setInputFilter().

  Within getInputFilter(), we instantiate an InputFilter and then add the inputs that we require. We add one input for each property that we wish to filter or validate. For the id field we add an int filter as we only need integers. For the text elements, we add two filters, StripTags and StringTrim, to remove unwanted HTML and unnecessary white space. We also set them to be required and add a StringLength validator to ensure that the user doesn't enter more characters than we can store into the database.
  */
  public function setInputFilter(InputFilterInterface $inputFilter)
  {
      throw new DomainException(sprintf(
          '%s does not allow injection of an alternate input filter',
          __CLASS__
      ));
  }

  public function getInputFilter()
  {
      if ($this->inputFilter) {
          return $this->inputFilter;
      }

      $inputFilter = new InputFilter();

      $inputFilter->add([
          'name' => 'id',
          'required' => true,
          'filters' => [
              ['name' => ToInt::class],
          ],
      ]);

      $inputFilter->add([
          'name' => 'artist',
          'required' => true,
          'filters' => [
              ['name' => StripTags::class],
              ['name' => StringTrim::class],
          ],
          'validators' => [
              [
                  'name' => StringLength::class,
                  'options' => [
                      'encoding' => 'UTF-8',
                      'min' => 1,
                      'max' => 100,
                  ],
              ],
          ],
      ]);

      $inputFilter->add([
          'name' => 'title',
          'required' => true,
          'filters' => [
              ['name' => StripTags::class],
              ['name' => StringTrim::class],
          ],
          'validators' => [
              [
                  'name' => StringLength::class,
                  'options' => [
                      'encoding' => 'UTF-8',
                      'min' => 1,
                      'max' => 100,
                  ],
              ],
          ],
      ]);

      $this->inputFilter = $inputFilter;
      return $this->inputFilter;
  }
}

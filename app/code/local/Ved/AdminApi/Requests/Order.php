<?php

/**
 * Class Ved_AdminApi_Requests_Order
 * @property array $data
 * @property \PHPixie\Validate\Results\Result\Root $result
 */
class Ved_AdminApi_Requests_Order
{
    protected $data;
    private $result;

    /**
     * Ved_AdminApi_Requests_Order constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return \PHPixie\Validate\Results\Result\Root
     * @internal param array $data
     */
    public function validate()
    {
        $validate = new \PHPixie\Validate();
        $validator = $validate->validator(function ($value) {
            $value->document(function ($document) {
                $document
                    ->allowExtraFields()
                    ->field('province_code', function ($name) {
                        $name
                            ->required()
                            ->filter(function ($filter) {
                                $filter
                                    ->alpha()
                                    ->minLength(3);
                            });
                    })
                    ->field('name', function ($home) {
                        $home
                            ->required()
                            ->filter(array(
                                'alpha',
                                'minLength' => array(10)
                            ));
                    })
                    ->field('age', function ($age) {
                        $age
                            ->required()
                            ->filter('numeric');
                    })
                    ->field('type', function ($home) {
                        $home
                            ->required()
                            ->callback(function ($result, $value) {
                                if (!in_array($value, array('fairy', 'pixie'))) {
                                    $result->addMessageError("Type can be either 'fairy' or 'pixie'");
                                }
                            });
                    });
            });
        });
        $this->result = $validator->validate($this->data);
        return $this->result;
    }

    /**
     * @return array
     * @internal param \PHPixie\Validate\Results\Result\Root $root
     */
    public function getError()
    {
        $result = [];
        foreach ($this->result->invalidFields() as $fieldResult) {
            $result[$fieldResult->path()] = [];
            foreach ($fieldResult->errors() as $error) {
                $result[$fieldResult->path()][] = $error->asString();
            }
        }
        return $result;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if (isset($this->data[$key]))
            return $this->data[$key];
        return $default;
    }
}
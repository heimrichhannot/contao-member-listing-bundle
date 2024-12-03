<?php

namespace HeimrichHannot\MemberListingBundle\Member;

use Contao\CoreBundle\Image\Studio\Figure;
use Contao\MemberModel;
use Contao\Model;
use Contao\UserModel;
use Contao\Validator;
use Spatie\SchemaOrg\Person;
use Spatie\SchemaOrg\PostalAddress;

class Member
{
    private array $data;
    private array $dirty = [];
    private ?Figure $figure;
    private ?string $website;

    public function __construct(array|MemberModel|UserModel $data, ?Figure $figure = null)
    {
        if ($data instanceof Model) {
            $data = $data->row();
        }

        if (!isset($data['name']) && isset($data['firstname'], $data['lastname'])) {
            $data['name'] = $data['firstname'] . ' ' . $data['lastname'];
        }

        $this->data = $data;
        $this->figure = $figure;
    }

    public function __get(string $name): mixed
    {
        if (isset($this->data[$name])) {
            $this->dirty[] = $name;

            switch ($name) {
                case 'website':
                    return $this->website();
                case 'figure':
                    return $this->figure();
            }

            return $this->data[$name];
        }

        throw new \InvalidArgumentException(sprintf('Property "%s" does not exist.', $name));
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function figure(): ?Figure
    {
        return $this->figure;
    }

    public function website(): ?string
    {
        if (!isset($this->website)) {
            if (empty($this->data['website'])) {
                $this->website = null;
            } elseif (!str_starts_with($this->data['website'], 'http')) {
                $this->website = 'https://' . $this->data['website'];
            } elseif (str_starts_with($this->data['website'], 'http://')) {
                $this->website = str_replace('http://', 'https://', $this->data['website']);
            } else {
                $this->website = $this->data['website'];
            }
        }

        return $this->website;
    }

    public function getSchemaOrgData(): array
    {
        $person = new Person();
        $person->name($this->data['name']);

        $address = new PostalAddress();

        foreach ($this->dirty as $key) {
            if (in_array($key, ['name', 'firstname', 'lastname', 'figure'])) {
                continue;
            }

            $value = $this->data[$key];

            switch ($key) {
                case 'postal':
                    $address->postalCode($value);
                    continue 2;
                case 'street':
                    $address->streetAddress($value);
                    continue 2;
                case 'city':
                    $address->addressLocality($value);
                    continue 2;
                case 'phone':
                    $person->telephone($value);
                    continue 2;
                case 'fax':
                    $person->faxNumber($value);
                    continue 2;
                case 'website':
                    $person->url($this->website());
                    continue 2;
            }

            if (!method_exists(Person::class, $key)) {
                continue;
            }

            if (Validator::isUuid($value)) {
                continue;
            }

            $person->{$key}($value);
        }

        if (!empty($address->getProperties())) {
            $person->address($address);
        }

        if (isset($this->figure)) {
            $person->image($this->figure->getSchemaOrgData());
        }

        return $person->toArray();
    }
}
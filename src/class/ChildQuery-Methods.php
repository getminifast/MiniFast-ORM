    public function set__COLUMN_FORMATED_NAME__($__COLUMN_NAME__)
    {
        parent::set('__COLUMN_NAME__', $__COLUMN_NAME__);
        return $this;
    }

    public function findBy__COLUMN_FORMATED_NAME__(string $__COLUMN_NAME__)
    {
        parent::findBy('__COLUMN_NAME__', $__COLUMN_NAME__);
        return $this;
    }

    public function filterBy__COLUMN_FORMATED_NAME__($__COLUMN_NAME__, $criteria = parent::EQUALS)
    {
        parent::filterBy('__COLUMN_NAME__', $__COLUMN_NAME__, $criteria);
        return $this;
    }

    public function count__COLUMN_FORMATED_NAME__($name)
    {
        parent::count('__COLUMN_NAME__', $name);
        return $this;
    }

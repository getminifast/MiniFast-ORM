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

    public function filterBy__COLUMN_FORMATED_NAME__($__COLUMN_NAME__)
    {
        parent::filterBy('__COLUMN_NAME__', $__COLUMN_NAME__, parent::EQUALS);
        return $this;
    }

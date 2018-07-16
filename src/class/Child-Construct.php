    public function __construct()
    {
        parent::__construct('__TABLE_NAME__');
    }

    public function getColumns()
    {
        return $this->vars;
    }


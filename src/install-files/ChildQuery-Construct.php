    private $base = '__TABLE_FORMATED_NAME__';

    public function __construct($table)
    {
        parent::__construct($table);
        parent::setBase($this->base);
    }

    public static function create(string $table = '__TABLE_NAME__')
    {
        return new __TABLE_FORMATED_NAME__Query($table);
    }

    public function getColumns()
    {
        $class = $this->base;
        $base = new $class();

        return $base->getColumns();
    }


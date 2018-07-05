    public function __construct($table)
    {
        parent::__construct($table);
    }

    public static function create(string $table = '__TABLE_NAME__')
    {
        return new UserQuery($table);
    }


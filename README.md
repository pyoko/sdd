# Shortest Driving Distance (SDD)

Just a simple Shortest Driving Distance finder using GoogleMap API.

### Storage
For the sake of simplicity, this small app uses File as our storage system. However, in case of scale out horizontally, using database system as storage system would be the best. That being said, we just need to build new storage service for database. For example:

```php
<?php
// src\SDD\Storage\MySQLStorage.php
namespace App\SDD\Storage;

use App\SDD\Storage\StorageInterface;

class MySQLStorage implements StorageInterface
{
    public function store($token, $data) 
    {
        // ... some code here
    }
    
    public function get($token) 
	{
        // ... some code here
    }
	
    public function update($token, $data)
    {
        // ... some code here
    }
	
    public function delete($token)
    {
        // ... some code here
    }
}
```

And change the storage service in services.yaml

```
# config/services.yaml
...
service.storage:
    class: App\SDD\Storage\MySQLStorage
...
```

### Google API Key

Change the Google API key with your API key in **.env** file, and for the testing change the "GOOGLE_API_KEY" enviroment in **phpunit.xml.dist** as well.
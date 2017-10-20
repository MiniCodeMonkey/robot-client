# Hetzner Robot API Client

> *Note:* This is just a copy of the Hetzner API client, that has been namespaced and composer/packagist support added.
> The source code can be found here: https://robot.your-server.de/downloads/robot-client.zip


## Usage

```php
use Hetzner\Robot\Client;

$robot = new Client('https://robot-ws.your-server.de', 'login', 'password');

// retrieve all failover ips
$results = $robot->failoverGet();

foreach ($results as $result)
{
  echo $result->failover->ip . "\n";
  echo $result->failover->server_ip . "\n";
  echo $result->failover->active_server_ip . "\n";
}

// retrieve a specific failover ip
$result = $robot->failoverGet('123.123.123.123');

echo $result->failover->ip . "\n";
echo $result->failover->server_ip . "\n";
echo $result->failover->active_server_ip . "\n";

// switch routing
try
{
  $robot->failoverRoute('123.123.123.123', '213.133.104.190');
}
catch (RobotClientException $e)
{
  echo $e->getMessage() . "\n";
}
```

> Full API documentation is available here: https://robot.your-server.de/doc/webservice/en.html

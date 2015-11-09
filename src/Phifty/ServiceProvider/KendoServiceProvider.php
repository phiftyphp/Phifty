<?php
namespace Phifty\ServiceProvider;
use Kendo\SecurityPolicy\SecurityPolicyModule;
use Kendo\RuleLoader\RuleLoader;
use Kendo\RuleLoader\SchemaRuleLoader;
use Kendo\RuleMatcher\AccessRuleMatcher;
use Kendo\Authorizer\Authorizer;
use Kendo\IdentifierProvider\ActorIdentifierProvider;
use Kendo\Operation\GeneralOperation;
use LazyRecord\ConnectionManager;
use Phifty\Kernel;

class KendoService
{
    protected $kernel;

    protected $options;

    protected $ruleLoader;

    public function __construct(Kernel $kernel, array $options)
    {
        $this->kernel = $kernel;
        $this->options = $options;
        if (isset($options['SecurityPolicies'])) {
            $policies = $options['SecurityPolicies'];
        }
        if (!isset($options['RuleLoader']['PDORuleLoader'])) {
            throw new Exception('Missing RuleLoader settings');
        }

    }

    public function getRuleLoader()
    {
        if ($this->ruleLoader) {
            return $this->ruleLoader;
        }
        $dataSource = $this->options['RuleLoader']['PDORuleLoader']['DataSource'];
        $connectionManager = ConnectionManager::getInstance();
        $conn = $connectionManager->getConnection($dataSource);
        $this->ruleLoader = new PDORuleLoader;
        $this->ruleLoader->load($conn);
        return $this->ruleLoader;
    }

    public function getAuthorizer()
    {
        if ($this->authorizer) {
            return $this->authorizer;
        }
        $authorizer = new Authorizer;
        $accessRuleMatcher = new AccessRuleMatcher($this->getRuleLoader());
        $authorizer->addMatcher($accessRuleMatcher);
        return $authorizer;
    }

    public function authorize($operation, $resource)
    {
        static $authorizer;
        $authorizer = $this->getAuthorizer();
        return $authorizer->authorize($this->kernel->currentUser, $operation, $resource);
    }

    public function authorizeByActor($actor, $operation, $resource)
    {
        static $authorizer;
        $authorizer = $this->getAuthorizer();
        return $authorizer->authorize($actor, $operation, $resource);
    }
}

class KendoServiceProvider extends BaseServiceProvider
{
    public function getId() { return 'access_control'; }

    public function register($kernel, $options = array())
    {
        $self = $this;
        $kernel->accessControl = function() use ($self, $kernel, $options) {
            return new KendoService($kernel, $options);
            /*
            $actor = new NormalUser;
            $ret = $authorizer->authorize($actor, GeneralOperation::VIEW, 'products');
            var_dump($ret);
            */
        };
    }
}

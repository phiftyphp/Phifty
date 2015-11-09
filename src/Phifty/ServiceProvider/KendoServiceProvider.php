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

class KendoService
{
    protected $options;

    protected $ruleLoader;

    public function __construct(array $options)
    {
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
}

class KendoServiceProvider extends BaseServiceProvider
{
    public function getId() { return 'access_control'; }

    public function register($kernel, $options = array())
    {
        $self = $this;
        $kernel->accessControl = function() use ($self, $kernel, $options) {
            return new KendoService($options);
            /*
            $actor = new NormalUser;
            $ret = $authorizer->authorize($actor, GeneralOperation::VIEW, 'products');
            var_dump($ret);
            */
        };
    }
}

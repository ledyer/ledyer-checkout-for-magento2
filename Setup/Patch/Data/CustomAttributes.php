<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Setup\Patch\Data;

use Exception;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\ResourceModel\Attribute as AttributeResource;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class CustomAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetup
     */
    private $customerSetup;

    /**
     * @var AttributeResource
     */
    private $attributeResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeResource $attributeResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeResource $attributeResource,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetup = $customerSetupFactory->create(['setup' => $moduleDataSetup]);
        $this->attributeResource = $attributeResource;
        $this->logger = $logger;
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Adds company id and reference fields for the customer entity
     *
     * @return void
     */
    public function apply()
    {
        // Start setup
        $this->moduleDataSetup->getConnection()->startSetup();
        try {
            // Add customer attribute with settings
            $this->customerSetup->addAttribute(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'company_id',
                [
                    'type' => 'varchar',
                    'label' => 'Company Id',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'sort_order' => 120,
                    'position' => 120,
                    'system' => 0,
                    'user_defined' => 1,
                ]
            );
            $this->customerSetup->addAttribute(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'reference_1',
                [
                    'type' => 'varchar',
                    'label' => 'Reference 1',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'sort_order' => 130,
                    'position' => 130,
                    'system' => 0,
                    'user_defined' => 1,
                ]
            );
            $this->customerSetup->addAttribute(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'reference_2',
                [
                    'type' => 'varchar',
                    'label' => 'Reference 2',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'sort_order' => 140,
                    'position' => 140,
                    'system' => 0,
                    'user_defined' => 1,
                ]
            );
            $attributes = ['company_id', 'reference_1', 'reference_2'];
            foreach ($attributes as $attr) {
                $this->customerSetup->addAttributeToSet(
                    CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                    CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                    null,
                    $attr
                );

                // Get the newly created attribute's model
                $attribute = $this->customerSetup->getEavConfig()
                    ->getAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $attr);

                $attribute->setData('used_in_forms', [
                    'adminhtml_customer','customer_account_create','customer_account_edit'
                ]);

                $this->attributeResource->save($attribute);
            }

        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Config;

class MetadataConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Api\Config\MetadataConfig
     */
    protected $metadataConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\Config\Reader
     */
    protected $serviceConfigReaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\MetadataObjectInterfaceFactory
     */
    protected $attributeMetadataFactoryMock;

    /**
     * Prepare parameters
     */
    public function setUp()
    {
        $this->serviceConfigReaderMock = $this->getMockBuilder('\Magento\Framework\Api\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeMetadataFactoryMock = $this->getMockBuilder(
            '\Magento\Framework\Api\MetadataObjectInterfaceFactory'
        )->setMethods(['create'])->disableOriginalConstructor()->getMock();

        $this->metadataConfig = new \Magento\Framework\Api\Config\MetadataConfig(
            $this->serviceConfigReaderMock,
            $this->attributeMetadataFactoryMock
        );
    }

    /**
     * Test caching
     */
    public function testCaching()
    {
        $dataObjectClassName = 'Magento\Customer\Model\Data\Address';
        $attributeCode = 'street';
        $allAttributes = [
            $dataObjectClassName => [
                $attributeCode => 'value',
            ]
        ];
        $this->serviceConfigReaderMock->expects($this->once())
            ->method('read')
            ->willReturn($allAttributes);

        $attributeMock = $this->getMock('\Magento\Framework\Api\MetadataObjectInterface');
        $attributeMock->expects($this->exactly(2))
            ->method('setAttributeCode')
            ->with($attributeCode)
            ->will($this->returnSelf());
        $this->attributeMetadataFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($attributeMock);

        $attributes = $this->metadataConfig->getCustomAttributesMetadata($dataObjectClassName);
        $this->assertEquals($attributeMock, $attributes[$attributeCode]);
        $attributes = $this->metadataConfig->getCustomAttributesMetadata($dataObjectClassName);
        $this->assertEquals($attributeMock, $attributes[$attributeCode]);
    }
}

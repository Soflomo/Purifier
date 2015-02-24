<?php
/**
 * Copyright (c) 2013 Jurian Sluiman.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://soflomo.com
 */

namespace Soflomo\Purifier\Factory;

use DomainException;
use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_Definition;
use RuntimeException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HtmlPurifierFactory implements FactoryInterface
{
    /**
     * {@inheritdocs}
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $configService = $sl->get('config');
        $moduleConfig  = $configService['soflomo_purifier'];
        $config        = $moduleConfig['config'];

        if ($moduleConfig['standalone']) {
            if (! file_exists($moduleConfig['standalone_path'])) {
                throw new RuntimeException('Could not find standalone purifier file');
            }

            include $moduleConfig['standalone_path'];
        }

        $purifierConfig = HTMLPurifier_Config::createDefault();
        foreach ($config as $key => $value) {
            $purifierConfig->set($key, $value);
        }

        foreach ($moduleConfig['definitions'] as $type => $methods) {
            $definition = $purifierConfig->getDefinition($type, true, true);

            if (! $definition instanceof HTMLPurifier_Definition) {
                throw new DomainException('Invalid definition type specified');
            }

            foreach ($methods as $method => $args) {
                call_user_func_array(array($definition, $method), $args);
            }
        }

        $purifier = new HTMLPurifier($purifierConfig);
        return $purifier;
    }
}

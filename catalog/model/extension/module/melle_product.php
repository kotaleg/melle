<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModuleMelleProduct extends Controller
{
    private $codename = 'melle_product';
    private $route = 'extension/module/melle_product';
    private $type = 'module';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);

        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/pro_patch/db');
        $this->load->model('extension/pro_patch/load');
    }

    public function applyOldOptionsForCurrent($oldOptions, $currentOptions)
    {
        sort($oldOptions);
        sort($currentOptions);

        foreach ($currentOptions as $cOptionK => $cOption) {
            foreach ($cOption['product_option_value'] as $cOptionValueK => $cOptionValue) {
                if (!isset($oldOptions[$cOptionK]['option_id'])
                || !isset($oldOptions[$cOptionK]['product_option_id'])) {
                    continue;
                }

                if (!isset($oldOptions[$cOptionK]['product_option_value'][$cOptionValueK]['option_value_id'])
                || !isset($oldOptions[$cOptionK]['product_option_value'][$cOptionValueK]['product_option_value_id'])) {
                    continue;
                }

                if (!isset($oldOptions[$cOptionK]['product_option_value'][$cOptionValueK]['selected'])) {
                    continue;
                }

                if ((int) $oldOptions[$cOptionK]['option_id']
                    != (int) $currentOptions[$cOptionK]['option_id']) {
                    continue;
                }
                if ((int) $oldOptions[$cOptionK]['product_option_id']
                    != (int) $currentOptions[$cOptionK]['product_option_id']) {
                    continue;
                }
                if ((int) $oldOptions[$cOptionK]['product_option_value'][$cOptionValueK]['option_value_id']
                    != (int) $currentOptions[$cOptionK]['product_option_value'][$cOptionValueK]['option_value_id']) {
                    continue;
                }
                if ((int) $oldOptions[$cOptionK]['product_option_value'][$cOptionValueK]['product_option_value_id']
                    != (int) $currentOptions[$cOptionK]['product_option_value'][$cOptionValueK]['product_option_value_id']) {
                    continue;
                }

                $currentOptions[$cOptionK]['product_option_value'][$cOptionValueK]['selected']
                    = (bool) $oldOptions[$cOptionK]['product_option_value'][$cOptionValueK]['selected'];

                // $currentOptions[$cOptionK]['product_option_value'][$cOptionValueK]['disabled_by_selection'] = true;
            }
        }

        return $currentOptions;
    }

    public function getOptionsForCart($options)
    {
        $optionsForCart = array();
        foreach ($options as $optionK => $option) {
            foreach ($option['product_option_value'] as $optionValueK => $optionValue) {
                if ($optionValue['selected'] == true) {
                    $optionsForCart[(string) $option['product_option_id']] = $optionValue['product_option_value_id'];
                }
            }
        }
        sort($activeOptions);
        return $optionsForCart;
    }

    public function getOptionsForOneClick($options)
    {
        $optionsForOneClick = array();
        foreach ($options as $optionK => $option) {
            foreach ($option['product_option_value'] as $optionValueK => $optionValue) {
                if ($optionValue['selected'] == true) {
                    $optionsForOneClick[] = array(
                        'option_name' => $option['name'],
                        'option_value_name' => $optionValue['name'],
                    );
                }
            }
        }
        sort($activeOptions);
        return $optionsForOneClick;
    }

    public function getActiveOptionsForComparison($options)
    {
        $activeOptions = array();
        foreach ($options as $optionK => $option) {
            foreach ($option['product_option_value'] as $optionValueK => $optionValue) {
                if ($optionValue['selected'] == true) {
                    $activeOptions[] = "{$option['option_id']}+{$optionValue['option_value_id']}";
                }
            }
        }
        sort($activeOptions);
        return $activeOptions;
    }

    public function getAvailableOptionsForComparison($options)
    {
        $availableOptions = array();
        foreach ($options as $optionK => $option) {
            foreach ($option['product_option_value'] as $optionValueK => $optionValue) {
                $availableOptions[] = "{$option['option_id']}+{$optionValue['option_value_id']}";
            }
        }
        return $availableOptions;
    }

    public function filterPossibleCombinations($fullCombinations, $options)
    {
        $availableOptions = $this->getAvailableOptionsForComparison($options);
        foreach ($fullCombinations as $combK => $comb) {
            foreach ($comb['required'] as $required) {
                if (!in_array("{$required['option_a']}+{$required['option_value_a']}", $availableOptions)) {
                    unset($fullCombinations[$combK]);
                }
            }
        }
        return $fullCombinations;
    }

    public function getActiveCombination($fullCombinations, $activeOptions)
    {
        foreach ($fullCombinations as $combK => $comb) {
            if (count($activeOptions) != count($comb['required'])) {
                continue;
            }

            $optionsToCompare = $this->prepareCombinationOptionsForCompare($comb['required']);

            foreach ($optionsToCompare as $oK => $o) {
                if (isset($activeOptions[$oK]) && $activeOptions[$oK] === $o) {
                    unset($optionsToCompare[$oK]);
                }
            }

            if (count($optionsToCompare) === 0) {
                return $comb;
            }
        }
    }

    private function prepareCombinationOptionsForCompare($combinationOptions)
    {
        $optionsToCompare = array();
        foreach ($combinationOptions as $required) {
            $optionsToCompare[] = "{$required['option_a']}+{$required['option_value_a']}";
        }
        sort($optionsToCompare);
        return $optionsToCompare;
    }

    public function getCartQuantityForOptions($optionsForCart)
    {
        $this->load->model('checkout/cart');
        $cartData = $this->model_checkout_cart->getCart();

        if (!$optionsForCart) {
            return;
        }

        foreach ($cartData['products'] as $cartProduct) {
            if (count($cartProduct['optionsForCompare']) != count($optionsForCart)) {
                continue;
            }

            foreach ($optionsForCart as $oK => $o) {
                if (!isset($cartProduct['optionsForCompare'][$oK])
                || $cartProduct['optionsForCompare'][$oK] === $o) {
                    continue;
                }
            }

            return $cartProduct['quantity'];
        }
    }

    public function getFirstPartialCombination($fullCombinations, $activeOptions)
    {
        if (empty($activeOptions)) {
            return array_pop($fullCombinations);
        }

        foreach ($fullCombinations as $combK => $comb) {
            $optionsToCompare = $this->prepareCombinationOptionsForCompare($comb['required']);

            foreach ($optionsToCompare as $oK => $o) {
                if (!in_array($o, $activeOptions)) {
                    continue;
                }

                return $comb;
            }
        }
    }

    public function applyPartialCombination($options, $combination)
    {
        foreach ($combination['required'] as $required) {
            foreach ($options as $optionK => $option) {
                if ((int) $option['option_id'] != (int) $required['option_a']) {
                    continue;
                }
                foreach ($option['product_option_value'] as $optionValueK => $optionValue) {
                    if ((int) $optionValue['option_value_id'] != (int) $required['option_value_a']) {
                        continue;
                    }
                    $options[$optionK]['product_option_value'][$optionValueK]['selected'] = true;
                }
            }
        }
        return $options;
    }

    public function updateDisabled($options, $combinations)
    {
        $activeOptions = $this->getActiveOptionsForComparison($options);
        $allowed = array();

        foreach ($combinations as $combK => $comb) {
            if ($comb['quantity'] <= 0) {
                continue;
            }

            if (count($activeOptions) != count($comb['required'])) {
                continue;
            }

            $optionsToCompare = $this->prepareCombinationOptionsForCompare($comb['required']);
            $optionsToMerge = $optionsToCompare;

            foreach ($optionsToCompare as $oK => $o) {
                if (isset($activeOptions[$oK]) && $activeOptions[$oK] === $o) {
                    unset($optionsToCompare[$oK]);
                }
            }

            if (count($optionsToCompare) === 0) {
                $allowed = array_merge($allowed, $optionsToMerge);
            }
        }

        foreach ($options as $optionK => $option) {
            foreach ($option['product_option_value'] as $optionValueK => $optionValue) {
                if (!in_array("{$option['option_id']}+{$optionValue['option_value_id']}", $allowed)) {
                    $options[$optionK]['product_option_value'][$optionValueK]['disabled_by_selection'] = true;
                }
            }
        }

        return $options;
    }

    public function selectOption($options, $optionId, $optionValueId, $productOptionId, $productOptionValueId)
    {
        foreach ($options as $optionK => $option) {
            if ((int)$option['option_id'] == (int)$optionId
            && (int)$option['product_option_id'] == (int)$productOptionId) {
                foreach ($option['product_option_value'] as $optionValueK => $optionValue) {
                    if ((int)$optionValue['option_value_id'] == (int)$optionValueId
                    && (int)$optionValue['product_option_value_id'] == (int)$productOptionValueId) {
                        $options[$optionK]['product_option_value'][$optionValueK]['selected'] = true;
                    } else {
                        $options[$optionK]['product_option_value'][$optionValueK]['selected'] = false;
                    }
                }
            }
        }
        return $options;
    }

    public function unselectAllBut($options, $optionId, $optionValueId, $productOptionId, $productOptionValueId)
    {
        foreach ($options as $optionK => $option) {
            foreach ($option['product_option_value'] as $optionValueK => $optionValue) {
                if ((int)$option['option_id'] == (int)$optionId
                && (int)$option['product_option_id'] == (int)$productOptionId) {
                    if ((int)$optionValue['option_value_id'] == (int)$optionValueId
                    && (int)$optionValue['product_option_value_id'] == (int)$productOptionValueId) {
                        $options[$optionK]['product_option_value'][$optionValueK]['selected'] = true;
                        continue;
                    }
                }
                $options[$optionK]['product_option_value'][$optionValueK]['selected'] = false;
            }
        }
        return $options;
    }
}

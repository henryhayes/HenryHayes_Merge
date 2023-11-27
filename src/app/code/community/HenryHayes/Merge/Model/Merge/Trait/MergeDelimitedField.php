<?php
trait HenryHayes_Merge_Model_Merge_Trait_MergeDelimitedField
{
    private $_mergeDelimitedFieldTestData = [
        // Correct spacing
        [
            'expected'      => 'one, two, three, four, five, six',
            'source'        => 'one, two, three',
            'destination'   => 'four, five, six',
        ],
        // Correct spacing
        [
            'expected'      => 'four, five, six',
            'source'        => '',
            'destination'   => 'four, five, six',
        ],
        // Inconsistent spacing
        [
            'expected'      => 'one, two, three, four, five, six',
            'source'        => 'one, two,three',
            'destination'   => 'four,five, six',
        ],
        // Inconsistent spacing
        [
            'expected'      => 'four, five, six',
            'source'        => '',
            'destination'   => 'four, five,six',
        ],
        // Random comma
         [
            'expected'      => 'four, five, six',
            'source'        => '',
            'destination'   => 'four, five,six,',
        ],
        // Random comma and random spacing
        [
            'expected'      => 'four, five, six',
            'source'        => '',
            'destination'   => ' four, five,six , ',
        ],
        // Random comma and random spacing
        [
            'expected'      => 'one, two, three, six',
            'source'        => 'one, two,three,,',
            'destination'   => 'six,',
        ],
        // Single string in source
        [
            'expected'      => 'one',
            'source' => 'one',
            'destination' => '',
        ],
        // Single string in destination
        [
            'expected'      => 'two',
            'source' => '',
            'destination' => 'two',
        ],
    ];


    /**
     * Takes delimited or un-delimited text and outputs the two merged by delimiter.
     * The value present in destination takes priority if exists in both.
     * 
     * Can take delimiter wrapped in whitespace either side.
     * 
     * @param   string $source
     * @param   string $destination
     * @param   string $delimiter
     * @return string
     */
    function mergeDelimitedField($source, $destination, $delimiter = ', ')
    {
        $trimmedDelimiter = trim($delimiter);

        $sources   = (array)array_filter(array_map('trim', explode($trimmedDelimiter, $source)));
        $dsestinations  = (array)array_filter(array_map('trim', explode($trimmedDelimiter, $destination)));

        $merged = array_merge($sources, $dsestinations);

        return count($merged) > 1 ? implode($delimiter, $merged) : reset($merged);
    }

    /**
     * Like a unit test, this self-tests the mergeDelimitedField method.
     * 
     * @return boolean
     */
    function selfTestMergeDelimitedData()
    {
        foreach ($this->_mergeDelimitedFieldTestData as $expected => $data) {

            $expected = array_shift($data);

            $actual = call_user_func_array('mergeDelimitedField', $data);

            if (!($expected == $actual)) {
                
                if (method_exists($this, 'addMessage')) {
                    
                    $this->addMessage(sprintf("'%s' NOT '%s'", $expected, $actual));
                }
                
                return false;
            }
        }

        return true;
    }
}
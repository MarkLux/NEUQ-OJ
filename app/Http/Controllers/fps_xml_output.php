<fps version="1.1" url="http://code.google.com/p/freeproblemset/">
    <generator name="HUSTOJ" url="http://code.google.com/p/hustoj/"/>
    <?php foreach ($problems as $problem){?>
        <item>
            <title><![CDATA[<?php echo $problem['title']; ?>]]></title>
            <time_limit unit="s"><![CDATA[<?php echo $problem['time_limit']; ?>]]></time_limit>
            <memory_limit unit="mb"><![CDATA[<?php echo $problem['memory_limit']; ?>]]></memory_limit>
            <description><?php echo $problem['description']; ?></description>
            <input><![CDATA[<?php echo $problem['input']; ?>]]></input>
            <ouptput><![CDATA[<?php echo $problem['output']; ?>]]></ouptput>
            <sample_input><![CDATA[<?php echo $problem['sample_input']; ?>]]></sample_input>
            <sample_output><![CDATA[<?php echo $problem['sample_output']; ?>]]></sample_output>
            <?php foreach($problem['test_input'] as $testIn) { ?>
            <test_input><![CDATA[<?php echo $testIn;?>]]></test_input>
            <?php } ?>
            <?php foreach($problem['test_output'] as $testOut) { ?>
            <test_output><![CDATA[<?php echo $testOut;?>]]></test_output>
            <?php } ?>
            <hint><![CDATA[<?php echo $problem['hint']; ?>]]></hint>
            <source><![CDATA[<?php echo $problem['source']; ?>]]></source>
        </item>
    <?php } ?>
</fps>
<?php

Kerisy::import('Kerisy.BBCode.Tag');

class Kerisy_BBCode
{
	private $_tags = array(
		'i' => 'Kerisy_BBCode_Tag_I',
		'b' => 'Kerisy_BBCode_Tag_B',
		'url' => 'Kerisy_BBCode_Tag_Url',
		'color' => 'Kerisy_BBCode_Tag_Color',
		'code' => 'Kerisy_BBCode_Tag_Code',
		'quote' => 'Kerisy_BBCode_Tag_Quote'
	);

	public function convertToBBCode($content)
	{
		$content = $this->filterInvalidTags($content);
		
		foreach ($this->_tags as $tag_class_name)
		{
			Kerisy::import(str_replace('_', '.', $tag_class_name));
		
			$Tag = new $tag_class_name;
			$content = $Tag->convertToBBCode($content);
		}
		
		$content = preg_replace(array('/<br[^>]*?>/U', '/<[^>]+?>/U'), array("\n", ''), $content);
		
		return $content;
	}
	
	public function findBBCodeTags($content)
	{
		// 初始化公用成员变量
		$find_tags = array();
		
		if (preg_match_all('/(\[(\/?)(\w+)([^\]]+)?\])/', $content, $matches, PREG_OFFSET_CAPTURE))
		{
			//ee($matches);
			$current_index = 0;
			for ($i = 0; $i < count($matches[0]); $i++)
			{
				$tag['tag'] = strtolower($matches[3][$i][0]);

				if (!isset($this->_tags[$tag['tag']]))
				{
					continue;
				}
				
				Kerisy::import(str_replace('_', '.', $this->_tags[$tag['tag']]));
				
				$Tag = new $this->_tags[$tag['tag']];
				$Tag->setTag($tag['tag']);
				$Tag->setCloseTag($matches[2][$i][0] ? true : false);
				$Tag->setPosition($matches[0][$i][1]);
				$Tag->setLength(strlen($matches[0][$i][0]));
				$Tag->setParamsFromString(isset($matches[4][$i][0]) ? $matches[4][$i][0] : '');
				
				if (!$Tag->isEffective())
				{
					continue;
				}
				
				if (!$Tag->isCloseTag())
				{
					if ($Tag->isSingle())
					{
						$find_tags[$current_index] = $Tag;
						$current_index++;
						continue;
					}
				}
				else 
				{
					// 如果是关闭的就检测一下上面是否有匹配到的开启标签
					if (count($find_tags))
					{
						$max = count($find_tags)-1;
						// 从后往前查找
						for ($j = $max; $j >= 0; $j--)
						{
							if ($Tag->getTag() == $find_tags[$j]->getTag() && $find_tags[$j]->isCloseTag() == false && $find_tags[$j]->isActived() == false)
							{
								$find_tags[$j]->setActived(true);
								$Tag->setActived(true);
								
								if ($Tag->ignoreBBCode())
								{
									$find_tags[$j]->setTagContent(substr($content, ($find_tags[$j]->getPosition()+$find_tags[$j]->getLength())), $Tag->getPosition());
									$find_tags[$j]->setLength($Tag->getPosition() - $find_tags[$j]->getPosition());
								
									$current_index = $j+1;
									if ($max-$j > 0) {
										for ($k=$j+1; $k<=$max; $k++)
										{
											unset($find_tags[$k]);
										}
									}
								}
								
								break;
							}
						}
					}
				}

				$find_tags[$current_index] = $Tag;
				$current_index++;
			}
		}
		
		return $find_tags;
	}
	
	public function parseBBCode($content)
	{
		if (!$content || !trim($content))
		{
			return NUll;
		}

		$content = preg_replace("/\n/", '<br />', $content);
		
		$find_tags = $this->findBBCodeTags($content);

		$i=(count($find_tags)-1);
		for ( ; $i >= 0; $i--)
		{
			// 跳过无效标签
			if (!$find_tags[$i]->isActived())
			{
				continue;
			}

			$content = substr($content, 0, $find_tags[$i]->getPosition()) . $find_tags[$i]->getHtml() . substr($content, $find_tags[$i]->getPosition() + $find_tags[$i]->getLength(), strlen($content));
		}
		
		return $content;
	}
	
	public function filterInvalidTags($content)
	{
		if (!$content || !trim($content))
		{
			return NUll;
		}
		
		$find_tags = $this->findBBCodeTags($content);

		$i=(count($find_tags)-1);
		for ( ; $i >= 0; $i--)
		{
			// 删除无效标签
			if (!$find_tags[$i]->isActived())
			{
				$content = substr($content, 0, $find_tags[$i]->getPosition()) . '' . substr($content, $find_tags[$i]->getPosition() + $find_tags[$i]->getLength(), strlen($content));
			}
		}
		
		return $content;
	}
	
	public function parseHtml($content)
	{
		
	}
}

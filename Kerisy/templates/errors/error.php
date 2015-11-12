<html>
  <?php
// Unique error identifier
  $error_id = uniqid('error');
  ?>
  <style type="text/css">
	#system_error { background: #ddd; font-size: 1em; font-family:sans-serif; text-align: left; color: #111; }
	#system_error h1,
	#system_error h2 { margin: 0; padding: 1em; font-size: 1em; font-weight: normal; background: #376290; color: #fff; }
	#system_error h1 a,
	#system_error h2 a { color: #fff; }
	#system_error h2 { background: #222; }
	#system_error h3 { margin: 0; padding: 0.4em 0 0; font-size: 1em; font-weight: normal; }
	#system_error p { margin: 0; padding: 0.2em 0; }
	#system_error a { color: #1b323b; }
	#system_error pre { overflow: auto; white-space: pre-wrap; }
	#system_error table { width: 100%; display: block; margin: 0 0 0.4em; padding: 0; border-collapse: collapse; background: #fff; }
	#system_error table td { border: solid 1px #ddd; text-align: left; vertical-align: top; padding: 0.4em; }
	#system_error div.content { padding: 0.4em 1em 1em; overflow: hidden; }
	#system_error pre.source { margin: 0 0 1em; padding: 0.4em; background: #fff; border: dotted 1px #b7c680; line-height: 1.2em; }
	#system_error pre.source span.line { display: block; }
	#system_error pre.source span.highlight { background: #D7E6F0; }
	#system_error pre.source span.line span.number { color: #666; }
	#system_error ol.trace { display: block; margin: 0 0 0 2em; padding: 0; list-style: decimal; }
	#system_error ol.trace li { margin: 0; padding: 0; }
	.js .collapsed { display: none; }
  </style>
  <?php if (DEBUG_MODE === true) : ?>
    <script type="text/javascript">
  	document.documentElement.className = document.documentElement.className + ' js';
  	function koggle(elem)
  	{
  	  elem = document.getElementById(elem);

  	  if (elem.style && elem.style['display'])
  		// Only works with the "style" attr
  		var disp = elem.style['display'];
  	  else if (elem.currentStyle)
  		// For MSIE, naturally
  		var disp = elem.currentStyle['display'];
  	  else if (window.getComputedStyle)
  		// For most other browsers
  		var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');

  	  // Toggle the state of the "display" style
  	  elem.style.display = disp == 'block' ? 'none' : 'block';
  	  return false;
  	}
    </script>
  <?php endif ?>
  <div id="system_error">
	<h1><span class="type"><?php echo $type ?> [ <?php echo $code ?> ]:</span> <span class="message"><?php echo $message ?></span></h1>
	<?php if (DEBUG_MODE === true) : ?>
      <div id="<?php echo $error_id ?>" class="content">
  	  <p><span class="file"><?php echo Kerisy_Debug::path($file) ?> [ <?php echo $line ?> ]</span></p>
		<?php echo Kerisy_Debug::source($file, $line) ?>
  	  <ol class="trace">
		  <?php foreach (Kerisy_Debug::trace($trace) as $i => $step): ?>
			<li>
			  <p>
				<span class="file">
				  <?php if ($step['file']): $source_id = $error_id . 'source' . $i; ?>
	  			  <a href="#<?php echo $source_id ?>" onclick="return koggle('<?php echo $source_id ?>')"><?php echo Kerisy_Debug::path($step['file']) ?> [ <?php echo $step['line'] ?> ]</a>
				  <?php else: ?>
	  			  {PHP internal call}
				  <?php endif ?>
				</span>
				&raquo;
				<?php echo $step['function'] ?>(<?php if ($step['args']): $args_id = $error_id . 'args' . $i; ?><a href="#<?php echo $args_id ?>" onclick="return koggle('<?php echo $args_id ?>')"><?php echo 'arguments' ?></a><?php endif ?>)
			  </p>
			  <?php if (isset($args_id)): ?>
	  		  <div id="<?php echo $args_id ?>" class="collapsed">
	  			<table cellspacing="0">
					<?php foreach ($step['args'] as $name => $arg): ?>
					  <tr>
						<td><code><?php echo $name ?></code></td>
						<td><pre><?php echo Kerisy_Debug::dump($arg) ?></pre></td>
					  </tr>
					<?php endforeach ?>
	  			</table>
	  		  </div>
			  <?php endif ?>
			  <?php if (isset($source_id)): ?>
	  		  <pre id="<?php echo $source_id ?>" class="source collapsed"><code><?php echo $step['source'] ?></code></pre>
			  <?php endif ?>
			</li>
			<?php unset($args_id, $source_id); ?>
		  <?php endforeach ?>
  	  </ol>
      </div>
      <h2><a href="#<?php echo $env_id = $error_id . 'environment' ?>" onclick="return koggle('<?php echo $env_id ?>')">Environment</a></h2>
      <div id="<?php echo $env_id ?>" class="content collapsed">
		<?php $included = get_included_files() ?>
  	  <h3><a href="#<?php echo $env_id = $error_id . 'environment_included' ?>" onclick="return koggle('<?php echo $env_id ?>')">Included files</a> (<?php echo count($included) ?>)</h3>
  	  <div id="<?php echo $env_id ?>" class="collapsed">
  		<table cellspacing="0">
			<?php foreach ($included as $file): ?>
			  <tr>
				<td><code><?php echo Kerisy_Debug::path($file) ?></code></td>
			  </tr>
			<?php endforeach ?>
  		</table>
  	  </div>
		<?php $included = get_loaded_extensions() ?>
  	  <h3><a href="#<?php echo $env_id = $error_id . 'environment_loaded' ?>" onclick="return koggle('<?php echo $env_id ?>')">Loaded extensions</a> (<?php echo count($included) ?>)</h3>
  	  <div id="<?php echo $env_id ?>" class="collapsed">
  		<table cellspacing="0">
			<?php foreach ($included as $file): ?>
			  <tr>
				<td><code><?php echo Kerisy_Debug::path($file) ?></code></td>
			  </tr>
			<?php endforeach ?>
  		</table>
  	  </div>
		<?php foreach (array('_SESSION', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER') as $var): ?>
		  <?php if (empty($GLOBALS[$var]) OR ! is_array($GLOBALS[$var])) continue ?>
		  <h3><a href="#<?php echo $env_id = $error_id . 'environment' . strtolower($var) ?>" onclick="return koggle('<?php echo $env_id ?>')">$<?php echo $var ?></a></h3>
		  <div id="<?php echo $env_id ?>" class="collapsed">
			<table cellspacing="0">
			  <?php foreach ($GLOBALS[$var] as $key => $value): ?>
	  		  <tr>
	  			<td><code><?php echo $key; ?></code></td>
	  			<td><pre><?php echo Kerisy_Debug::dump($value) ?></pre></td>
	  		  </tr>
			  <?php endforeach ?>
			</table>
		  </div>
		<?php endforeach ?>
      </div>
	<?php endif ?>
  </div>
</html>
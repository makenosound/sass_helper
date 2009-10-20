<?php
 
  Class extension_sass_helper extends Extension
  {
    public $workspace_position = NULL;
    public $sass_exec = "sass";
    
    /*-------------------------------------------------------------------------
      Extension definition
    -------------------------------------------------------------------------*/
    
    public function about()
    {
      return array(
        'name' => 'Sass Helper',
        'version' => '1.0',
        'release-date' => '2009-10-20',
        'author' => array(
          'name' => 'Max Wheeler',
          'email' => 'max@makenosound.com',
        )
      );
    }
 
    public function getSubscribedDelegates()
    {
      return array(
        array(
          'page' => '/frontend/',
          'delegate' => 'FrontendOutputPostGenerate',
          'callback' => 'find_matches'          
        )
      );
    }
    
  	/*-------------------------------------------------------------------------
  		Delegates
  	-------------------------------------------------------------------------*/
    public function find_matches(&$context)
    {
      $context['output'] = preg_replace_callback('/(\"|\')([^\"\']+).sass/', array(&$this, '__replace_matches'), $context['output']);
    }
    
  	/*-------------------------------------------------------------------------
  		Helpers
  	-------------------------------------------------------------------------*/
    private function __replace_matches($matches)
    {
      $this->workspace_position = strpos($matches[0], 'workspace');
      if (!$this->workspace_position) $this->workspace_position = 1;
      
      $path = DOCROOT . "/" . substr($matches[0], $this->workspace_position);
      $path = $this->__generate_css($path);
      $mtime = @filemtime($path);
      
      return str_replace('.sass', ($mtime ? '.css?' . 'mod-' . $mtime : NULL), $matches[0]);
    }
    
    private function __generate_css($filename)
    {
      # Setup .css and .sass filenames
      $sass_filename = $filename;
      $css_filename = str_replace('.sass', '.css', $filename);

      # If Sass doesn't exist, throw an error in the CSS
      if ( ! file_exists($sass_filename))
      {
        file_put_contents($css_filename, "/** Error: Sass file not found **/");
      }
      else if (!file_exists($css_filename) OR filemtime($css_filename) < filemtime($sass_filename))
      {
        @unlink($css_filename);
        # Generate .css via shell command
        exec($this->sass_exec . escapeshellcmd($sass_filename) . ' ' . escapeshellcmd($css_filename));
      }
      return $css_filename;
    }
  }

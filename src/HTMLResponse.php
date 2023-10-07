<?php
	
	namespace Lynphp\Lyn;
	
	use DOMDocument;
	use Exception;
	
	class HTMLResponse
	{
		public HTTPRequest $request;
		private array $slots=[];
		private string $template='';
		private bool $asPage=true;
		private array $scripts=[];
		private array $styles=[];
		public string $title='';
		private string $sessionId='';
		public function __construct(HTTPRequest $request)
		{
			session_start();
			
			$this->request=$request;
			$this->handle();
		}
		final public function setTitle(string $title):void{
			$this->title=$title;
		}
		final public function renderScripts():void
		{
			foreach ($this->scripts as $script){
				echo PHP_EOL.$script.PHP_EOL;
			}
		}
	
		final public function renderStyles():void
		{
			foreach ($this->styles as $style){
				echo PHP_EOL.$style.PHP_EOL;
			}
		}
		final public function renderAsFragment(){
			$this->template='';
			$this->asPage=false;
		}
		private function addScriptContent(string $script):void{
			$this->scripts[]='<script>'.PHP_EOL.$script.PHP_EOL.'</script>';
		}
		private function addStyleContent(string $style):void{
			$this->styles[]='<style>'.PHP_EOL.$style.PHP_EOL.'</style>';
		}
		final public function addSlot(string $location, string $htmlContent):void{
			$this->slots[$location] = $htmlContent;
		}
		
		final public function getSlots():array{
			return $this->slots;
		}
		
		final public function handle():void{
			
			$scriptPath = $this->locate();
			ob_start();
			extract(['HTMLResponse'=>$this], EXTR_OVERWRITE);
			require $scriptPath;
			$content = ob_get_clean();
			$this->addSlot('main',$content);
		}
		final public function getComponent(string $component):string{
			
			ob_start();
			extract(['HTMLResponse'=>$this], EXTR_OVERWRITE);
			require base_path.'/src/components/'.$component.'.php';
			$content = ob_get_clean();
			#scope script
			//$script = $this->getElementContent('script', $content);
			//$this->addScriptContent($script);
			//$style = $this->getElementContent('style', $content);
			//$this->addStyleContent($style);
			#scope css
			//$content = $this->removeTag('script',$content);
			return $content;
		}
		private function removeTag(string $tag, string $document):string{
			
			$dom = new DOMDocument();
			$dom->loadHTML($document);
			$scripts = $dom->getElementsByTagName('script');
			$scripts[0]->parentNode->removeChild($scripts[0]);
			//$dom->removeChild($scripts[0]);
			return $dom->saveHTML();
		}
		private function getElementContent(string $tag, string $document):string{
			$dom = new DOMDocument();
			$dom->loadHTML($document);
			$scripts = $dom->getElementsByTagName($tag);
			$script_content='';
			foreach ($scripts as $script) {
				$script_content.= $script->nodeValue;
				// Do something with $script_content
			}
			return $script_content;
		}
		final public function locate(): string
		{
			$segments = $this->getSegments($this->request->getURI());
			$currentDir = base_path.'/src/routes';
			$lastSegment='';
			foreach ($segments as $segment) {
				if(is_dir($currentDir.'/'.$segment)) {
					$currentDir .= '/' . $segment;
					$lastSegment = $segment;
				}else if(is_file($currentDir.'/'.$segment.'.php')){
					$lastSegment = $segment;
				} else {
					$this->request->addSlug($segment);
				}
			}
			return $this->getScript($currentDir, $lastSegment);
		}
		
		private function getScript(string $currentDir, string $last_segment):string{
			
			if (file_exists($currentDir.'/'.$last_segment.'.php')) {
				return $currentDir.'/'.$last_segment.'.php';
			}
			
			if(file_exists($currentDir.'/index.php')) {
				return $currentDir.'/index.php';
			}
			
			if(file_exists($currentDir.'/'.$last_segment.'/'.$last_segment.'.php')) {
				return $currentDir.'/'.$last_segment.'/'.$last_segment.'.php';
			}
			
			if(file_exists($currentDir.'/'.$last_segment.'/index.php')) {
				return $currentDir.'/'.$last_segment.'/index.php';
			}
			return base_path.'/src/routes/page_not_found.php';
		}
		private function getSegments(string $uri):array{
			return explode('/', trim(explode('?',$uri,)[0], '/'));
		}
		
		/**
		 * @throws Exception
		 */
		final public function send():void
		{
			$html_page='';
			$html_template='';
			if($this->asPage){
				ob_start();
				extract(['HTMLResponse'=>$this], EXTR_OVERWRITE);
				include_once base_path.'/src/templates/html.template.php';
				$html_page = ob_get_clean();
			}
			ob_start();
			extract(['HTMLResponse'=>$this], EXTR_OVERWRITE);
			if(!empty($this->template) && $html_page !== ''){
				include_once base_path.'/src/templates/'.$this->template.'.template.php';
				$html_template = ob_get_clean();
				$html_page = str_replace('<slot id="template"></slot>',$html_template, $html_page);
			}else if(!empty($this->template)){
				include_once base_path.'/src/templates/'.$this->template.'.template.php';
				$html_page = ob_get_clean();
			}
			if(count($this->slots)>0){
				if(isset($this->slots['main']) && $html_page !== ''){
					$html_page = str_replace('<slot id="main"></slot>',$this->slots['main'], $html_page);
				}else	if((isset($this->slots['main']) && $html_page !== '') || !$this->asPage) {
					$html_page = $this->slots['main'];
				}else {
					throw new Exception('Main Slot is not set or template not found!');
				}
			}
			if(count($this->slots)>1){
				foreach ($this->slots as $key=>$html){
					$html_page = str_replace('<slot id="'.$key.'"></slot>',$html, $html_page);
				}
			}
			echo $html_page;
		}
		
		final public function setTemplate(string $template):void
		{
			$this->template=$template;
		}
	}
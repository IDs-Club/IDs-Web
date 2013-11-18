<?php 
/*
 * This class is written based entirely on the work found below
 * www.techbytes.co.in/blogs/2006/01/15/consuming-rss-with-php-the-simple-way/
 * All credit should be given to the original author
*/

class RSSParser
{
    // ===================//
    //Instance vars       //
    // ===================//

    /* Feed URI */
    var $feed_uri;

    /* Associative array containing all the feed items */
    var $data;

    /* Store RSS Channel Data in an array */
    var $channel_data;

    /*  Boolean variable which indicates whether an RSS feed was unavailable */
    var $feed_unavailable;

    // ================ //
    // Constructor      //
    // ================ //
    function RSSParser($params) {

          $this->feed_uri = $params['url'];
          $this->current_feed["title"] = '';
          $this->current_feed["description"] = '';
          $this->current_feed["link"] = '';
          $this->data = array();
          $this->channel_data = array();

          //Attempt to parse the feed
          $this->parse();
    }

    // =============== //
    // Methods         //
    // =============== //
    function parse() {

      //Parse the document
      $rawFeed = @file_get_contents($this->feed_uri);

	  if($rawFeed === FALSE)
	  {
		  return;
	  }

      $xml = new SimpleXmlElement($rawFeed);

      //Assign the channel data
      $this->channel_data['title'] = $xml->channel->title;
      $this->channel_data['description'] = $xml->channel->description;

      //Build the item array
      foreach ($xml->channel->item as $item)
      {
           $data = array();
           $data['title'] = (string)$item->title;
           $data['description'] = (string)$item->description;
           // 获取图片
           $preg = '/<span class="text-img-holder"><img src="(.+?)" width="(.+?)" height="(.+?)"\/><\/span>/s';
           preg_match($preg, $data['description'], $arr);
           $data['img_url'] = $arr[1];

           $data['pubDate'] = (string)date("M Y",strtotime($item->pubDate));
           $data['link'] = (string)$item->link;
           $this->data[] = $data;
      }
      return true;
  }

    /* Return the feeds one at a time: when there are no more feeds return false
     * @param No of items to return from the feed
     * @return Associative array of items
    */
    function getFeed($num) {
        $c = 0;
        $return = array();
        foreach($this->data AS $item)
        {
            $return[] = $item;
            $c++;
            if($c == $num) break;
        }
        return $return;
    }

    /* Return channel data for the feed */
    function & getChannelData() {
        $flag = false;
         if(!empty($this->channel_data)) {
            return $this->channel_data;
        } else {
            return $flag;
        }
    }

    /* Were we unable to retreive the feeds ?  */
    function errorInResponse() {
       return $this->feed_unavailable;
    }

}

/* End of file RSSParser.php */
/* Location: ./app/libraries/RSSParser.php */
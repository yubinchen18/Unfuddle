<?php
namespace Unfuddle;
use GuzzleHttp\Exception\RequestException;

class Unfuddle
{
	protected $client;
	protected $projectId;
	public $lastError = null;
	
	public function __construct($client, $projectId)
	{
		$this->client = $client; 
		$this->projectId = (int)$projectId;
	}
	
	public function __destruct()
	{
		if ($this->lastError !== null) {
			throw new \Exception($this->lastError);
		}
	}
	/**
	 * Shows a list of all messages within the project.
	 */
	public function getMessages()
	{
		$response = $this->client->request('GET', 'projects/'.$this->projectId.'/messages/')->getBody()->getContents();
		$xml = new \SimpleXMLElement($response);
		foreach ($xml->message as $message) {
			echo '<br>-------------------------------------------------------';
			echo '<br>-------------------------------------------------------';
			echo '<br>(Author ID: ' . $message->{'author-id'} . ')';
			echo '<br>(Message ID: ' . $message->{'id'} . ')';
			echo '<br>-------------------------------------------------------';
			echo '<br>Title: '.$message->{'title'};
			echo '<br>Body: '.$message->{'body'};
			echo '<br>-------------------------------------------------------';
			echo '<br>Created: ' . $message->{'created-at'};
			echo '<br>Last mod: ' . $message->{'updated-at'};
			echo '<br>-------------------------------------------------------';
			echo '<br>-------------------------------------------------------<br>';
		}
	}
	
	/**
	 * Creates a new message within the project.
	 * @param string $title
	 * @param string $body
	 */
	public function postMessage($title, $body)
	{
		$title = (string)$title;
		$body = (string)$body;
		$message =	"<message>
						<title>$title</title>
						<body>$body</body>
						<body-format>markdown</body-format>
					</message>";
		try {
			$this->client->request('POST', 'projects/'.$this->projectId.'/messages/', ['body'=>$message]);
		} catch (RequestException $e) {
			$this->lastError = (string)($e->getMessage()).". ".(string)($e->hasResponse()? $e->getResponse()->getBody()->getContents():'');
			return false;
		}
		return true;
	}
	

	/**
	 * Edits the content of an existing message.
	 * @param int $messageId
	 * @param string $title
	 * @param string $body
	 */
	public function updateMessage($messageId, $title, $body)
	{
		$id = (int)$messageId;
		$title = (string)$title;
		$body = (string)$body;
		$message = "<message>
						<id type='integer'>$id</id>
						<title>$title</title>
						<body>$body</body>
						<body-format>markdown</body-format>
					</message>";
		try {
			$this->client->request('PUT', 'projects/'.$this->projectId.'/messages/'.$id, ['body'=>$message]);
		} catch (RequestException $e) {
			$this->lastError = (string)($e->getMessage()).". ".(string)($e->hasResponse()? $e->getResponse()->getBody()->getContents():'');
			return false;
		}
		return true;
	}
	
	/**
	 * Delete an existing message within the project.
	 * @param int $messageId
	 */
	public function deleteMessage($messageId)
	{
		$id = (int)$messageId;
		try {
			$this->client->request('DELETE', 'projects/'.$this->projectId.'/messages/'.$id);
		} catch (RequestException $e) {
			$this->lastError = (string)($e->getMessage()).". ".(string)($e->hasResponse()? $e->getResponse()->getBody()->getContents():'');
			return false;
		}
		return true;
	}
	
	/**
	 * Shows a list of all tickets within the project.
	 */
	public function getTickets()
	{
		$response = $this->client->request('GET', 'projects/'.$this->projectId.'/tickets/')->getBody()->getContents();
		$xml = new \SimpleXMLElement($response);
		foreach ($xml->ticket as $ticket) {
			echo '<br>-------------------------------------------------------';
			echo '<br>-------------------------------------------------------';
			echo '<br>(Reporter ID: ' . $ticket->{'reporter-id'} . ')';
			echo '<br>(Assignee ID: ' . $ticket->{'assignee-id'} . ')';
			echo '<br>(Ticket ID: ' . $ticket->{'id'} . ')';
			echo '<br>(Project ID: ' . $ticket->{'project-id'} . ')';
			echo '<br>(Status: ' . $ticket->{'status'} . ')';
			echo '<br>(Priority: ' . $ticket->{'priority'} . ')';
			echo '<br>-------------------------------------------------------';
			echo '<br>Due on: '.$ticket->{'due-on'};
			echo '<br>Initial hours estimate: '.$ticket->{'hours-estimate-initial'};
			echo '<br>Current hours estimate: '.$ticket->{'hours-estimate-current'};
			echo '<br>-------------------------------------------------------';
			echo '<br>Summary: ' . $ticket->{'summary'};
			echo '<br>Full Description: ' . $ticket->{'description'};
			echo '<br>-------------------------------------------------------';
			echo '<br>Created: ' . $ticket->{'created-at'};
			echo '<br>Last mod: ' . $ticket->{'updated-at'};
			echo '<br>-------------------------------------------------------';
			echo '<br>-------------------------------------------------------<br>';
		}
	}
	
	public function getLastTicketId()
	{
		$body = '<request>
					<limit>1</limit>
					<page>1</page>
				</request>';
		$response = $this->client->request('GET', 'projects/'.$this->projectId.'/tickets/', ['body'=>$body])->getBody()->getContents();
		$xml = new \SimpleXMLElement($response);
		return (int)$xml->ticket[0]->id[0];
	}

	/**
	 * Creates a new ticket within the project.
	 * (note!: attachment not working yet, use postAttachment() instead)
	 * @param string $summary
	 * @param string $description
	 * @param int $priority
	 * @param float $hours
	 * @param string $dueOn as 'yyyy-mm-dd'
	 * @param string $attachmentPath
	 * @param string $attachmentFilename
	 */
	public function postTicket($summary, $description, $priority = 3, $hours = null, $dueOn = null, $attachmentPath = null, $attachmentFilename = null)
	{
		$summary = (string)$summary;
		$description = (string)$description;
		$priority = (int)$priority;
		$dueOn = (string)$dueOn;
		$hours = (float)$hours;
		$ticket =	"<ticket>
						<summary>$summary</summary>
						<description>$description</description>
						<priority>$priority</priority>
						<due-on type='date'>$dueOn</due-on>
						<hours-estimate-initial type='float'>$hours</hours-estimate-initial>
						<description-format>markdown</description-format>
					</ticket>";
		try {
			$this->client->request('POST', 'projects/'.$this->projectId.'/tickets/', ['body'=>$ticket]);
		} catch (RequestException $e) {
			$this->lastError = (string)($e->getMessage()).". ".(string)($e->hasResponse()? $e->getResponse()->getBody()->getContents():'');
			return false;
		}
		return true;
	}
	
	/**
	 * Delete an existing ticket within the project.
	 * @param int $ticketId
	 */
	public function deleteTicket($ticketId)
	{
		$id = (int)$ticketId;
		try {
			$this->client->request('DELETE', 'projects/'.$this->projectId.'/tickets/'.$id);
		} catch (RequestException $e) {
			$this->lastError = (string)($e->getMessage()).". ".(string)($e->hasResponse()? $e->getResponse()->getBody()->getContents():'');
			return false;
		}
		return true;
	}
	
	/**
	 * Shows a list of all attachements attached to an existing ticket
	 * @param int $ticketId
	 */
	public function getAttachments($ticketId)
	{
		$response = $this->client->request('GET', 'projects/'.$this->projectId.'/tickets/'.$ticketId.'/attachments/')->getBody()->getContents();
		$xml = new \SimpleXMLElement($response);
		foreach ($xml->attachment as $attachment) {
			echo '<br>-------------------------------------------------------';
			echo '<br>-------------------------------------------------------';
			echo '<br>(Ticket ID: ' . $attachment->{'parent-id'} . ')';
			echo '<br>(Project ID: ' . $attachment->{'project-id'} . ')';
			echo '<br>(Attachment ID: ' . $attachment->{'id'} . ')';
			echo '<br>(File name: ' . $attachment->{'filename'} . ')';
			echo '<br>(File size: ' . $attachment->{'size'} . ')';
			echo '<br>-------------------------------------------------------';
			echo '<br>Created: ' . $attachment->{'created-at'};
			echo '<br>Last mod: ' . $attachment->{'updated-at'};
			echo '<br>-------------------------------------------------------';
			echo '<br>-------------------------------------------------------<br>';
		}	
	}
	
	/**
	 * Creates a new attachment within an existing ticket.
	 * @param int $ticketId
	 * @param string $path
	 * @param string $filename
	 */
	public function postAttachment($ticketId, $path, $filename)
	{
		$path = (string)$path. (substr((string)$path, -1) == '\\' ? '': '\\');
		$file = ($path.(string)$filename);
		$body = fopen($file, 'rb');
		
		//upload
		$response = $this->client->request('POST', 'projects/'.$this->projectId.'/tickets/'.$ticketId.'/attachments/upload/', 
					['headers' => ['Content-Type' => 'application/octet-stream'],'body'=>$body])->getBody()->getContents();
		$xml = new \SimpleXMLElement($response);
		
		//attach
		$message ='<attachment>
					<filename>'.$filename.'</filename>
					<content-type>application/octet-stream</content-type>
					<upload>
						<key>'.$xml->key.'</key>
					</upload>
				</attachment>';
		try {
			$this->client->request('POST', 'projects/'.$this->projectId.'/tickets/'.$ticketId.'/attachments/', ['body'=>$message]);
		} catch (RequestException $e) {
			$this->lastError = (string)($e->getMessage()).". ".(string)($e->hasResponse()? $e->getResponse()->getBody()->getContents():'');
			return false;
		}
		return true;
	}
	
	/**
	 * Downloads an attachment attached to an existing ticket and saves it to the computer
	 * @param int $ticketId
	 * @param int $attachmentId
	 * @param string $path
	 * @param string $filename
	 */
	public function downloadAttachment($ticketId, $attachmentId, $path, $filename)
	{
		$content = $this->client->request('GET', 'projects/'.$this->projectId.'/tickets/'.$ticketId.'/attachments/'.$attachmentId.'/download')->getBody()->getContents();
		$path = (string)$path. (substr((string)$path, -1) == '\\' ? '': '\\');
		$file = ($path.(string)$filename);
		$filestream = fopen($file, 'w');
		fwrite($filestream, $content);
		fclose($filestream);
	}
	
	/**
	 * Delete an existing attachment within a ticket.
	 * @param int $ticketId
	 * @param int $attachmentId
	 */
	public function deleteAttachment($ticketId, $attachmentId)
	{
		$ticketId = (int)$ticketId;
		$attachmentId = (int)$attachmentId;
		try {
			$this->client->request('DELETE', 'projects/'.$this->projectId.'/tickets/'.$ticketId.'/attachments/'.$attachmentId);
		} catch (RequestException $e) {
			$this->lastError = (string)($e->getMessage()).". ".(string)($e->hasResponse()? $e->getResponse()->getBody()->getContents():'');
			return false;
		}
		return true;
	}
	
	/**
	 * Shows a list of all comments within a ticket
	 * @param int $ticketId
	 */
	public function getComments($ticketId)
	{
		$response = $this->client->request('GET', 'projects/'.$this->projectId.'/tickets/'.$ticketId.'/comments/')->getBody()->getContents();
		$xml = new \SimpleXMLElement($response);
		foreach ($xml->comment as $comment) {
			echo '<br>-------------------------------------------------------';
			echo '<br>-------------------------------------------------------';
			echo '<br>(Ticket ID: ' . $comment->{'parent-id'} . ')';
			echo '<br>(Author ID: ' . $comment->{'author-id'} . ')';
			echo '<br>(Comment ID: ' . $comment->{'id'} . ')';
			echo '<br>-------------------------------------------------------';
			echo '<br>Comment: ' . $comment->{'body'};
			echo '<br>-------------------------------------------------------';
			echo '<br>Created: ' . $comment->{'created-at'};
			echo '<br>Last mod: ' . $comment->{'updated-at'};
		}
	}
	
	/**
	 * Creates a new comment within a ticket
	 * @param int $ticketId
	 * @param string $comment
	 */
	public function postComment($ticketId, $comment)
	{
		$comment = (string)$comment;
		$body = "<comment>
					<body>$comment</body>
				</comment>";
		try {
			$this->client->request('POST', 'projects/'.$this->projectId.'/tickets/'.$ticketId.'/comments/', ['body' => $body]);
		} catch (RequestException $e) {
			$this->lastError = (string)($e->getMessage()).". ".(string)($e->hasResponse()? $e->getResponse()->getBody()->getContents():'');
			return false;
		}
		return true;
	}
	
	/**
	 * Deletes an exisiting comment within a ticket
	 * @param int $ticketId
	 * @param int $commentId
	 */
	public function deleteComment($ticketId, $commentId)
	{
		try {
			$this->client->request('DELETE', 'projects/'.$this->projectId.'/tickets/'.$ticketId.'/comments/'.$commentId);
		} catch (RequestException $e) {
			$this->lastError = (string)($e->getMessage()).". ".(string)($e->hasResponse()? $e->getResponse()->getBody()->getContents():'');
			return false;
		}
		return true;
	}
}
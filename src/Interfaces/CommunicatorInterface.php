<?php

namespace Picahoo\Communicator\Interfaces;

interface CommunicatorInterface{

	/**
     * get credential.
     *
     * @return array
     */
	public function getCredentials();

	/**
     * refresh token.
     *
     * @return string
     */
	public function refreshToken();

	/**
     * generate token.
     *
     * @return array
     */
	public function generateToken();

	/**
     * get token.
     *
     * @return string
     */
	public function getToken();

	/**
     * get contact list.
     *
     * @return array
     */
	public function getContactList();

	/**
     * add contact.
     *
     * @return array
     */
	public function addContact($requestData);

	/**
     * Check contact exist , if contact does not exist than create new one.
     *
     * @return array
     */
	public function checkContactByEmail($email);

	/**
     * Send email.
     *
     * @return array
     */
	public function sendEmail($to, $message, $subject);

	/**
     * Send email by contact id.
     *
     * @return array
     */
	public function sendEmailByContactId($contactId,$message, $subject);

	/**
     * Transform contact list to array.
     *
     * @return array
     */
	public function transformContacts($contactLists);

}
-- phpMyAdmin SQL Dump
-- version 2.6.0-pl2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Aug 22, 2005 at 02:24 PM
-- Server version: 3.23.58
-- PHP Version: 4.3.11
-- 
-- Database: `GUI`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `gui`
-- 

CREATE TABLE tm_theme (
  theme_id varchar(75) NOT NULL default '0',
  theme_display_name varchar(255) NOT NULL default '',
  theme_description text NOT NULL,
  theme_template tinyint(1) NOT NULL,
  theme_custom_lev varchar(75) NOT NULL,
  theme_owner_id varchar(75) NOT NULL default ''
) 

CREATE TABLE tm_style_collection (
  collection_id varchar(75) NOT NULL default '0',
  collection_display_name varchar(255) NOT NULL default '',
  collection_description text,
  collection_class_selector varchar(255) NOT NULL,
  collection_selector varchar(255) NOT NULL,
  collection_component varchar(255) NOT NULL default 'BLANK',
  collection_index varchar(255) NOT NULL,
  collection_class varchar(255) NOT NULL default '',
  FK_theme_id varchar(255) NOT NULL
) 

CREATE TABLE tm_style_property (
  property_id varchar(75) NOT NULL default '0',
  property_name varchar(255),
  property_display_name varchar(255) default '',
  property_description text,
  FK_collection_id varchar(75)
) 

CREATE TABLE tm_style_component (
	component_id varchar(75) NOT NULL default '0',
	component_class_name varchar(255) NOT NULL,
	component_value varchar(255) NOT NULL,
	FK_property_id varchar(75) NOT NULL
) 
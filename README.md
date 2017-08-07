# WSU People Directory

[![Build Status](https://travis-ci.org/washingtonstateuniversity/WSU-People-Directory.svg?branch=master)](https://travis-ci.org/washingtonstateuniversity/WSU-People-Directory)

A WordPress plugin to maintain a central directory of people.

## Hooks

WSU People is configured by default to work on Washington State University's WordPress platform. However, several hooks are included that can be used to adapt the plugin so that it works in other setups.

### A dual purpose plugin

In its full capacity, the WSU People Directory plugin is activated globally on a multi-network/multisite WordPress installation. These hooks exist to differentiate between the main site that maintains a record of people and other sites that pull these records from a central source.

The `wsuwp_people_is_main_site` filter is available for indicating that a site is the main people directory. By default, this filter is `false` and the plugin assumes the role of a secondary site.

### REST API

The `wsu_people_directory_rest_url` filter is available to modify the default REST URL used for people records. This is attached to `people.wsu.edu` by default, but can be used with any WordPress installation.

Once a site has been designated as the main people directory using the `wsuwp_people_is_main_site` filter, the `wsuwp_people_directory_rest_url` should be updated to match that site's settings.

### Organization Data

The `wsuwp_people_get_organization_person_data` filter is available for providing organizational data about a person. This allows for the automatic collection of basic data that should be managed through active directory or some other means.

A specific return structure is expected. Data will be sanitized after retrieval:

```php
$return_data = array(
    'given_name' => '',
    'surname' => '',
    'title' => '',
    'office' => '',
    'street_address' => '',
    'telephone_number' => '',
    'email' => '',
);
```

## REST API

WSU's directory of people can best be consumed via the [WordPress REST API](http://v2.wp-api.org/):

* `https://people.wsu.edu/wp-json/wp/v2/people`

The general [documentation](http://v2.wp-api.org/) for the REST API is the best place to see how to interact with the API itself. We provide several custom fields with person records returned by the API.

Here is an raw overview of the properties attached to each object:

```
{
  "id":20,
  "date":"2015-07-24T12:16:22",
  "date_gmt":"2015-07-24T19:16:22",
  "guid":{
    "rendered":"http:\/\/people.wsu.edu\/?post_type=wsuwp_people_profile&#038;p=20"
  },
  "modified":"2017-08-07T13:13:26",
  "modified_gmt":"2017-08-07T20:13:26",
  "slug":"phillip-cable",
  "status":"publish",
  "type":"wsuwp_people_profile",
  "link":"https:\/\/people.wsu.edu\/profile\/phillip-cable\/",
  "title":{
    "rendered":"Phillip Cable"
  },
  "content":{
    "rendered":"<p>This is my personal biography<\/p>\n",
    "protected":false
  },
  "template":"",
  "tags":[],
  "university_category":[],
  "location":[
    2
  ],
  "organization":[
    390
  ],
  "classification":[
    409
  ],
  "nid":"pcable",
  "first_name":"Phillip",
  "last_name":"Cable",
  "position_title":"WORDPRESS DEVELOPER",
  "office":"ITB 2021",
  "address":"PO BOX 641227",
  "phone":"509-335-0383",
  "phone_ext":"",
  "email":"pcable@wsu.edu",
  "office_alt":"",
  "phone_alt":"",
  "email_alt":"",
  "address_alt":"",
  "website":"",
  "degree":[],
  "working_titles":[
    "Web Developer"
  ],
  "bio_unit":"<p>This is my unit biography.<\/p>",
  "bio_university":"<p>This is my university biography.<\/p>",
  "photos":[],
  "listed_on":[],
  "bio_college":"",
  "bio_lab":"",
  "bio_department":"",
  "cv_employment":"",
  "cv_honors":"",
  "cv_grants":"",
  "cv_publications":"",
  "cv_presentations":"",
  "cv_teaching":"",
  "cv_service":"",
  "cv_responsibilities":"",
  "cv_affiliations":"",
  "cv_experience":"",
  "cv_attachment":false,
  "profile_photo":"",
  "_links":{
    "self":[
      {
        "href":"https:\/\/people.wsu.edu\/wp-json\/wp\/v2\/people\/20"
      }
    ],
    "collection":[
      {
        "href":"https:\/\/people.wsu.edu\/wp-json\/wp\/v2\/people"
      }
    ],
    "about":[
      {
        "href":"https:\/\/people.wsu.edu\/wp-json\/wp\/v2\/types\/wsuwp_people_profile"
      }
    ],
    "version-history":[
      {
        "href":"https:\/\/people.wsu.edu\/wp-json\/wp\/v2\/people\/20\/revisions"
      }
    ],
    "wp:attachment":[
      {
        "href":"https:\/\/people.wsu.edu\/wp-json\/wp\/v2\/media?parent=20"
      }
    ],
    "wp:term":[
      {
        "taxonomy":"post_tag",
        "embeddable":true,
        "href":"https:\/\/people.wsu.edu\/wp-json\/wp\/v2\/tags?post=20"
      },
      {
        "taxonomy":"wsuwp_university_category",
        "embeddable":true,
        "href":"https:\/\/people.wsu.edu\/wp-json\/wp\/v2\/university_category?post=20"
      },
      {
        "taxonomy":"wsuwp_university_location",
        "embeddable":true,
        "href":"https:\/\/people.wsu.edu\/wp-json\/wp\/v2\/location?post=20"
      },
      {
        "taxonomy":"wsuwp_university_org",
        "embeddable":true,
        "href":"https:\/\/people.wsu.edu\/wp-json\/wp\/v2\/organization?post=20"
      },
      {
        "taxonomy":"classification",
        "embeddable":true,
        "href":"https:\/\/people.wsu.edu\/wp-json\/wp\/v2\/classification?post=20"
      }
    ],
    "curies":[
      {
        "name":"wp",
        "href":"https:\/\/api.w.org\/{rel}",
        "templated":true
      }
    ]
  }
}
```

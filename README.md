# CMS-Developer-Test
Notice : Please Place main-file.php and dataset.json in same folder

Create JSON file named dataset.json which contains data of Events

Create Custom Post type of name Events which has post type "tp_event"

Add metabox named "Additional Information About Event" against my custom post type Events also save additional data of post on update the post

Create Page named "Import Events" in admin Menu in which i created a button of name "Import Events" and after Clicking on it. Our scripts read json file ad get data from it if the id is already present in database then it updates our custom post events and if id is not available then it insert new post with draft status of event with its related information (id, title, about, organizer, email, lat, long and tags)

Create shortcode "Show_Events" of Upcoming Events sorted by their timestamps (closest events at the top) which displays upcoming events in table with information of ID, title, about, date and days left in event. Just Copy this shortcode and paste on any page displays the upcoming events (Notice: It Displays data after you import Events Successfully from the Import events page)

Create REST-API(custom url = 'yoursiteurl/wp-json/tp/events') which display all upcoming events in json format

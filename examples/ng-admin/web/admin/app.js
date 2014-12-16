/*global angular*/
(function () {
    "use strict";

    var app = angular.module('myApp', ['ng-admin']);

    app.directive('customPostLink', ['$location', function ($location) {
        return {
            restrict: 'E',
            template: '<a ng-click="displayPost(entry)">View&nbsp;post</a>',
            link: function ($scope) {
                $scope.displayPost = function (entry) {
                    var postId = entry.values.post_id;

                    $location.path('/edit/posts/' + postId);
                };
            }
        };
    }]);

    app.config(function (NgAdminConfigurationProvider, Application, Entity, Field, Reference, ReferencedList, ReferenceMany) {

        function truncate(value) {
            if (!value) {
                return '';
            }

            return value.length > 50 ? value.substr(0, 50) + '...' : value;
        }

        function pagination(page, maxPerPage) {
            return {
                _start: (page - 1) * maxPerPage,
                _end: page * maxPerPage
            };
        }

        var app = new Application('ng-admin backend demo') // application main title
            .baseApiUrl('http://localhost:8888/api'); // main API endpoint

        // define all entities at the top to allow references between them
        var post = new Entity('posts'); // the API endpoint for posts will be http://localhost:3000/posts/:id

        // set the application entities
        app
            .addEntity(post);

        // customize entities and views
        post.dashboardView()
            .title('Recent posts')
            .order(1) // display the post panel first in the dashboard
            .limit(5) // limit the panel to the 5 latest posts
            .pagination(pagination) // use the custom pagination function to format the API request correctly
            .addField(new Field('title').isDetailLink(true).map(truncate));

        post.listView()
            .title('All posts') // default title is "[Entity_name] list"
            .pagination(pagination)
            .addField(new Field('id').label('ID'))
            .addField(new Field('title')) // the default list field type is "string", and displays as a string
            .listActions(['show', 'edit', 'delete']);

        post.showView() // a showView displays one entry in full page - allows to display more data than in a a list
            .addField(new Field('id'))
            .addField(new Field('title'))
            .addField(new Field('body').type('wysiwyg'));

        post.creationView()
            .addField(new Field('title')) // the default edit field type is "string", and displays as a text input
            .addField(new Field('body').type('wysiwyg')) // overriding the type allows rich text editing for the body

        post.editionView()
            .title('Edit post "{{ entry.values.title }}"') // title() accepts a template string, which has access to the entry
            .actions(['list', 'show', 'delete']) // choose which buttons appear in the action bar
            .addField(new Field('title'))
            .addField(new Field('body').type('wysiwyg'));

        NgAdminConfigurationProvider.configure(app);
    });
}());

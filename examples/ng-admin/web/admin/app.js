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

        var app = new Application('Microrest demo')
            .baseApiUrl('http://localhost:8888/api');

        var post = new Entity('posts');

        var comment = new Entity('comments');

        var tag = new Entity('tags')
            .readOnly();

        app
            .addEntity(post)
            .addEntity(tag)
            .addEntity(comment);

        // post
        post.dashboardView()
            .title('Recent posts')
            .order(1)
            .limit(5)
            .pagination(pagination)
            .addField(new Field('title').isDetailLink(true).map(truncate));

        post.listView()
            .title('All posts')
            .pagination(pagination)
            .addField(new Field('id').label('ID'))
            .addField(new Field('title'))
            .listActions(['show', 'edit', 'delete'])
            .filterQuery(false);

        post.showView()
            .addField(new Field('id'))
            .addField(new Field('title'))
            .addField(new Field('body').type('wysiwyg'))
            .addField(new ReferencedList('comments')
                .targetEntity(comment)
                .targetReferenceField('post_id')
                .targetFields([
                    new Field('id'),
                    new Field('body').label('Comment')
                ])
            );

        post.creationView()
            .addField(new Field('title'))
            .addField(new Field('body').type('wysiwyg'))

        post.editionView()
            .title('Edit post "{{ entry.values.title }}"')
            .actions(['list', 'show', 'delete'])
            .addField(new Field('title'))
            .addField(new Field('body').type('wysiwyg'))
            .addField(new ReferencedList('comments')
                .targetEntity(comment)
                .targetReferenceField('post_id')
                .targetFields([
                    new Field('id'),
                    new Field('body').label('Comment')
                ])
            );

        // comment
        comment.dashboardView()
            .title('Last comments')
            .order(2)
            .limit(5)
            .pagination(pagination)
            .addField(new Field('id'))
            .addField(new Field('body').label('Comment').map(truncate))
            .addField(new Field()
                .type('template')
                .label('Actions')
                .template(function () {
                    return '<custom-post-link></custom-post-link>';
                })
            );

        comment.listView()
            .title('Comments')
            .description('List of all comments with an infinite pagination')
            .pagination(pagination)
            .addField(new Field('id').label('ID'))
            .addField(new Reference('post_id')
                .label('Post title')
                .map(truncate)
                .targetEntity(post)
                .targetField(new Field('title'))
            )
            .addField(new Field('body').map(truncate))
            .addField(new Field('created_at').label('Creation date').type('date'))
            .filterQuery(false);

        comment.creationView()
            .addField(new Reference('post_id')
                .label('Post title')
                .map(truncate)
                .targetEntity(post)
                .targetField(new Field('title'))
            )
            .addField(new Field('body').type('wysiwyg'))
            .addField(new Field('created_at')
                .label('Creation date')
                .type('date')
                .defaultValue(new Date())
            );

        comment.editionView()
            .addField(new Reference('post_id')
                .label('Post title')
                .map(truncate)
                .targetEntity(post)
                .targetField(new Field('title'))
            )
            .addField(new Field('body').type('wysiwyg'))
            .addField(new Field('created_at').label('Creation date').type('date'))
            .addField(new Field()
                .type('template')
                .label('Actions')
                .template('<custom-post-link></custom-post-link>')
            );

        comment.deletionView()
            .title('Deletion confirmation');

        // tag
        tag.dashboardView()
            .title('Recent tags')
            .order(3)
            .limit(10)
            .pagination(pagination)
            .addField(new Field('id').label('ID'))
            .addField(new Field('name'))
            .addField(new Field('published').label('Is published ?').type('boolean'));

        tag.listView()
            .infinitePagination(false)
            .pagination(pagination)
            .addField(new Field('id').label('ID'))
            .addField(new Field('name'))
            .addField(new Field('published').type('boolean'))
            .addField(new Field('custom')
                .type('template')
                .label('Upper name')
                .template('{{ entry.values.name.toUpperCase() }}')
            )
            .listActions(['show'])
            .filterQuery(false);

        tag.showView()
            .addField(new Field('name'))
            .addField(new Field('published').type('boolean'));

        NgAdminConfigurationProvider.configure(app);
    });
}());

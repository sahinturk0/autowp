import angular from 'angular';
import Module from 'app.module';
import template from './template.html';
import { AclService } from 'services/acl';
import './add';
import './edit';

const CONTROLLER_NAME = 'ModerPagesController';
const STATE_NAME = 'moder-pages';

angular.module(Module)
    .config(['$stateProvider',
        function config($stateProvider) {
            $stateProvider.state( {
                name: STATE_NAME,
                url: '/moder/pages',
                controller: CONTROLLER_NAME,
                controllerAs: 'ctrl',
                template: template,
                resolve: {
                    access: ['AclService', function (Acl) {
                        return Acl.inheritsRole('pages-moder', 'unauthorized');
                    }]
                }
            });
        }
    ])
    .controller(CONTROLLER_NAME, [
        '$scope', '$http', 'AclService',
        function($scope, $http, Acl) {
            $scope.pageEnv({
                layout: {
                    isAdminPage: true,
                    blankPage: false,
                    needRight: false
                },
                name: 'page/68/name',
                pageId: 68
            });
            
            var ctrl = this;
            ctrl.items = [];
            
            ctrl.canManage = false;
            Acl.isAllowed('hotlinks', 'manage').then(function(allow) {
                ctrl.canManage = !!allow;
            }, function() {
                ctrl.canManage = false;
            });
            
            var load = function() {
                $http({
                    method: 'GET',
                    url: '/api/page'
                }).then(function(response) {
                    ctrl.items = toPlainArray(response.data.items, 0);
                });
            };
            
            load();
            
            function toPlainArray(pages, level) {
                var result = [];
                angular.forEach(pages, function(page, i) {
                    page.level = level;
                    page.moveUp = i > 0;
                    page.moveDown = i < pages.length-1;
                    result.push(page);
                    angular.forEach(toPlainArray(page.childs, level+1), function(child) {
                        result.push(child);
                    });
                });
                return result;
            }
            
            ctrl.move = function(page, direction) {
                $http({
                    method: 'PUT',
                    url: '/api/page/' + page.id,
                    data: {
                        position: direction
                    }
                }).then(function(response) {
                    load();
                });
            };
            
            ctrl.deletePage = function(ev, page) {
                if (window.confirm('Would you like to delete page?')) {
                    $http({
                        method: 'DELETE',
                        url: '/api/page/' + page.id
                    }).then(function(response) {
                        load();
                    });
                }
            };
        }
    ]);

export default CONTROLLER_NAME;

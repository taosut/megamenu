var csfApp = angular.module('csf', ['ui.bootstrap', 'angularFileUpload']);
csfApp.directive("fileUpload",function(){
    return {
        restrict: "E",
        scope: {
            model: '=',
            showThumb: '@',
            uploadUrl: '@',
            styleClass: '@',
            fileTypeAllow: '@',
            fileSizeAllow: '@',
        },
        template: '<table style="width: 100%;table-layout: auto" class="{{styleClass}}">\
                      <tr>\
                        <td ng-if="showThumb">\
                          <img ng-src="{{model.url}}" ng-if="model != null && model.url !=null" alt="{{model.name}}" height="100px">\
                          <div ng-if="uploader.queue.length == 1" ng-thumb="{file:item._file, height: 100 }"></div>\
                        </td>\
                        <td style="padding: 0px 10px">\
                          <input type="file" style="display: inline-block" nv-file-select uploader="uploader" ng-show="uploader.queue.length == 0">\
                          <button type="submit" class="btn btn-primary btm-xs" ng-click="upload()" ng-show="uploader.queue.length == 1">Upload</button>\
                        </td>\
                        <td style="width: 100%">\
                          <uib-progressbar class="progress-striped" value="item.progress" ng-show="uploader.queue.length == 1">{{item.progress}}%</uib-progressbar>\
                        </td>\
                      </tr>\
                    </table>',
        controller: function($scope,FileUploader){
            $scope.fileSizeAllow = defaultValue($scope.fileSizeAllow,1024*1024*1024);
            $scope.fileTypeAllow = defaultValue($scope.fileTypeAllow,"jpg,jpeg,png,gif,pdf,doc,docx,key,ppt,pptx,pps,ppsx,odt,xls,xlsx,zip,mp3,m4a,ogg,wav,mp4,m4v,mov,wmv,avi,mpg,ogv,3gp,3g2,flv,mkv");

            uploader = new FileUploader({removeAfterUpload: true,url:$scope.uploadUrl});
            $scope.uploader = uploader;
            $scope.item = {progress:0};
            uploader.filters.push({
                name: 'fileFilter',
                fn: function(item /*{File|FileLikeObject}*/, options) {
                    if(this.queue.length > 1) return false;
                    if(item.size > $scope.fileSizeAllow) return false;
                    var type = ',' + item.name.slice(item.name.lastIndexOf('.') + 1) + ',';
                    return (','+$scope.fileTypeAllow+',').indexOf(type) !== -1;
                }
            });
            $scope.upload = function(){
                console.log(uploader.queue);
                uploader.uploadAll();
            };
            uploader.onAfterAddingFile = function(fileItem) {
                $scope.item = fileItem;
                console.log(uploader.queue.length,uploader.queue);
            };
            uploader.onSuccessItem = function(fileItem, response, status, headers) {
                $scope.model = response;
                console.info('onSuccessItem', fileItem, response);
            };
        }
    };
});
csfApp.directive('ngThumb', ['$window', function($window) {
    var helper = {
        support: !!($window.FileReader && $window.CanvasRenderingContext2D),
        isFile: function(item) {
            return angular.isObject(item) && item instanceof $window.File;
        },
        isImage: function(file) {
            var type =  '|' + file.type.slice(file.type.lastIndexOf('/') + 1) + '|';
            return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
        }
    };

    return {
        restrict: 'A',
        template: '<canvas/>',
        link: function(scope, element, attributes) {
            if (!helper.support) return;

            var params = scope.$eval(attributes.ngThumb);

            if (!helper.isFile(params.file)) return;
            if (!helper.isImage(params.file)) return;

            var canvas = element.find('canvas');
            var reader = new FileReader();

            reader.onload = onLoadFile;
            reader.readAsDataURL(params.file);

            function onLoadFile(event) {
                var img = new Image();
                img.onload = onLoadImage;
                img.src = event.target.result;
            }

            function onLoadImage() {
                var width = params.width || this.width / this.height * params.height;
                var height = params.height || this.height / this.width * params.width;
                canvas.attr({ width: width, height: height });
                canvas[0].getContext('2d').drawImage(this, 0, 0, width, height);
            }
        }
    };
}]);
function defaultValue(value,defV){
    return value != null ? value: defV;
}
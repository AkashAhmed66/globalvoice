// vite.config.js
import { defineConfig } from "file:///D:/xampp/htdocs/globalvoice/node_modules/vite/dist/node/index.js";
import laravel from "file:///D:/xampp/htdocs/globalvoice/node_modules/laravel-vite-plugin/dist/index.js";
import html from "file:///D:/xampp/htdocs/globalvoice/node_modules/@rollup/plugin-html/dist/es/index.js";
import { glob } from "file:///D:/xampp/htdocs/globalvoice/node_modules/glob/dist/esm/index.js";
function GetFilesArray(query) {
  return glob.sync(query);
}
var pageJsFiles = GetFilesArray("resources/js/*.js");
var pageAssetsJsFiles = GetFilesArray("resources/assets/js/*.js");
var vendorJsFiles = GetFilesArray("resources/assets/vendor/js/*.js");
var LibsJsFiles = GetFilesArray("resources/assets/vendor/libs/**/*.js");
var CoreScssFiles = GetFilesArray("resources/assets/vendor/scss/**/!(_)*.scss");
var LibsScssFiles = GetFilesArray("resources/assets/vendor/libs/**/!(_)*.scss");
var LibsCssFiles = GetFilesArray("resources/assets/vendor/libs/**/*.css");
var FontsScssFiles = GetFilesArray("resources/assets/vendor/fonts/**/!(_)*.scss");
function libsWindowAssignment() {
  return {
    name: "libsWindowAssignment",
    transform(src, id) {
      if (id.includes("jkanban.js")) {
        return src.replace("this.jKanban", "window.jKanban");
      } else if (id.includes("vfs_fonts")) {
        return src.replaceAll("this.pdfMake", "window.pdfMake");
      }
    }
  };
}
var vite_config_default = defineConfig({
  plugins: [
    laravel({
      input: [
        "resources/css/app.css",
        "resources/assets/css/demo.css",
        "resources/js/app.js",
        ...pageJsFiles,
        ...pageAssetsJsFiles,
        ...vendorJsFiles,
        ...LibsJsFiles,
        "resources/js/laravel-user-management.js",
        // Processing Laravel User Management CRUD JS File
        ...CoreScssFiles,
        ...LibsScssFiles,
        ...LibsCssFiles,
        ...FontsScssFiles
      ],
      refresh: true
    }),
    html(),
    libsWindowAssignment()
  ]
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJEOlxcXFx4YW1wcFxcXFxodGRvY3NcXFxcZ2xvYmFsdm9pY2VcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfZmlsZW5hbWUgPSBcIkQ6XFxcXHhhbXBwXFxcXGh0ZG9jc1xcXFxnbG9iYWx2b2ljZVxcXFx2aXRlLmNvbmZpZy5qc1wiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9pbXBvcnRfbWV0YV91cmwgPSBcImZpbGU6Ly8vRDoveGFtcHAvaHRkb2NzL2dsb2JhbHZvaWNlL3ZpdGUuY29uZmlnLmpzXCI7aW1wb3J0IHsgZGVmaW5lQ29uZmlnIH0gZnJvbSAndml0ZSc7XG5pbXBvcnQgbGFyYXZlbCBmcm9tICdsYXJhdmVsLXZpdGUtcGx1Z2luJztcbmltcG9ydCBodG1sIGZyb20gJ0Byb2xsdXAvcGx1Z2luLWh0bWwnO1xuaW1wb3J0IHsgZ2xvYiB9IGZyb20gJ2dsb2InO1xuXG4vKipcbiAqIEdldCBGaWxlcyBmcm9tIGEgZGlyZWN0b3J5XG4gKiBAcGFyYW0ge3N0cmluZ30gcXVlcnlcbiAqIEByZXR1cm5zIGFycmF5XG4gKi9cbmZ1bmN0aW9uIEdldEZpbGVzQXJyYXkocXVlcnkpIHtcbiAgcmV0dXJuIGdsb2Iuc3luYyhxdWVyeSk7XG59XG4vKipcbiAqIEpzIEZpbGVzXG4gKi9cbi8vIFBhZ2UgSlMgRmlsZXNcbmNvbnN0IHBhZ2VKc0ZpbGVzID0gR2V0RmlsZXNBcnJheSgncmVzb3VyY2VzL2pzLyouanMnKTtcbmNvbnN0IHBhZ2VBc3NldHNKc0ZpbGVzID0gR2V0RmlsZXNBcnJheSgncmVzb3VyY2VzL2Fzc2V0cy9qcy8qLmpzJyk7XG5cbi8vIFByb2Nlc3NpbmcgVmVuZG9yIEpTIEZpbGVzXG5jb25zdCB2ZW5kb3JKc0ZpbGVzID0gR2V0RmlsZXNBcnJheSgncmVzb3VyY2VzL2Fzc2V0cy92ZW5kb3IvanMvKi5qcycpO1xuXG4vLyBQcm9jZXNzaW5nIExpYnMgSlMgRmlsZXNcbmNvbnN0IExpYnNKc0ZpbGVzID0gR2V0RmlsZXNBcnJheSgncmVzb3VyY2VzL2Fzc2V0cy92ZW5kb3IvbGlicy8qKi8qLmpzJyk7XG5cbi8qKlxuICogU2NzcyBGaWxlc1xuICovXG4vLyBQcm9jZXNzaW5nIENvcmUsIFRoZW1lcyAmIFBhZ2VzIFNjc3MgRmlsZXNcbmNvbnN0IENvcmVTY3NzRmlsZXMgPSBHZXRGaWxlc0FycmF5KCdyZXNvdXJjZXMvYXNzZXRzL3ZlbmRvci9zY3NzLyoqLyEoXykqLnNjc3MnKTtcblxuLy8gUHJvY2Vzc2luZyBMaWJzIFNjc3MgJiBDc3MgRmlsZXNcbmNvbnN0IExpYnNTY3NzRmlsZXMgPSBHZXRGaWxlc0FycmF5KCdyZXNvdXJjZXMvYXNzZXRzL3ZlbmRvci9saWJzLyoqLyEoXykqLnNjc3MnKTtcbmNvbnN0IExpYnNDc3NGaWxlcyA9IEdldEZpbGVzQXJyYXkoJ3Jlc291cmNlcy9hc3NldHMvdmVuZG9yL2xpYnMvKiovKi5jc3MnKTtcblxuLy8gUHJvY2Vzc2luZyBGb250cyBTY3NzIEZpbGVzXG5jb25zdCBGb250c1Njc3NGaWxlcyA9IEdldEZpbGVzQXJyYXkoJ3Jlc291cmNlcy9hc3NldHMvdmVuZG9yL2ZvbnRzLyoqLyEoXykqLnNjc3MnKTtcblxuLy8gUHJvY2Vzc2luZyBXaW5kb3cgQXNzaWdubWVudCBmb3IgTGlicyBsaWtlIGpLYW5iYW4sIHBkZk1ha2VcbmZ1bmN0aW9uIGxpYnNXaW5kb3dBc3NpZ25tZW50KCkge1xuICByZXR1cm4ge1xuICAgIG5hbWU6ICdsaWJzV2luZG93QXNzaWdubWVudCcsXG5cbiAgICB0cmFuc2Zvcm0oc3JjLCBpZCkge1xuICAgICAgaWYgKGlkLmluY2x1ZGVzKCdqa2FuYmFuLmpzJykpIHtcbiAgICAgICAgcmV0dXJuIHNyYy5yZXBsYWNlKCd0aGlzLmpLYW5iYW4nLCAnd2luZG93LmpLYW5iYW4nKTtcbiAgICAgIH0gZWxzZSBpZiAoaWQuaW5jbHVkZXMoJ3Zmc19mb250cycpKSB7XG4gICAgICAgIHJldHVybiBzcmMucmVwbGFjZUFsbCgndGhpcy5wZGZNYWtlJywgJ3dpbmRvdy5wZGZNYWtlJyk7XG4gICAgICB9XG4gICAgfVxuICB9O1xufVxuXG5leHBvcnQgZGVmYXVsdCBkZWZpbmVDb25maWcoe1xuICBwbHVnaW5zOiBbXG4gICAgbGFyYXZlbCh7XG4gICAgICBpbnB1dDogW1xuICAgICAgICAncmVzb3VyY2VzL2Nzcy9hcHAuY3NzJyxcbiAgICAgICAgJ3Jlc291cmNlcy9hc3NldHMvY3NzL2RlbW8uY3NzJyxcbiAgICAgICAgJ3Jlc291cmNlcy9qcy9hcHAuanMnLFxuICAgICAgICAuLi5wYWdlSnNGaWxlcyxcbiAgICAgICAgLi4ucGFnZUFzc2V0c0pzRmlsZXMsXG4gICAgICAgIC4uLnZlbmRvckpzRmlsZXMsXG4gICAgICAgIC4uLkxpYnNKc0ZpbGVzLFxuICAgICAgICAncmVzb3VyY2VzL2pzL2xhcmF2ZWwtdXNlci1tYW5hZ2VtZW50LmpzJywgLy8gUHJvY2Vzc2luZyBMYXJhdmVsIFVzZXIgTWFuYWdlbWVudCBDUlVEIEpTIEZpbGVcbiAgICAgICAgLi4uQ29yZVNjc3NGaWxlcyxcbiAgICAgICAgLi4uTGlic1Njc3NGaWxlcyxcbiAgICAgICAgLi4uTGlic0Nzc0ZpbGVzLFxuICAgICAgICAuLi5Gb250c1Njc3NGaWxlc1xuICAgICAgXSxcbiAgICAgIHJlZnJlc2g6IHRydWVcbiAgICB9KSxcbiAgICBodG1sKCksXG4gICAgbGlic1dpbmRvd0Fzc2lnbm1lbnQoKVxuICBdXG59KTtcbiJdLAogICJtYXBwaW5ncyI6ICI7QUFBMlEsU0FBUyxvQkFBb0I7QUFDeFMsT0FBTyxhQUFhO0FBQ3BCLE9BQU8sVUFBVTtBQUNqQixTQUFTLFlBQVk7QUFPckIsU0FBUyxjQUFjLE9BQU87QUFDNUIsU0FBTyxLQUFLLEtBQUssS0FBSztBQUN4QjtBQUtBLElBQU0sY0FBYyxjQUFjLG1CQUFtQjtBQUNyRCxJQUFNLG9CQUFvQixjQUFjLDBCQUEwQjtBQUdsRSxJQUFNLGdCQUFnQixjQUFjLGlDQUFpQztBQUdyRSxJQUFNLGNBQWMsY0FBYyxzQ0FBc0M7QUFNeEUsSUFBTSxnQkFBZ0IsY0FBYyw0Q0FBNEM7QUFHaEYsSUFBTSxnQkFBZ0IsY0FBYyw0Q0FBNEM7QUFDaEYsSUFBTSxlQUFlLGNBQWMsdUNBQXVDO0FBRzFFLElBQU0saUJBQWlCLGNBQWMsNkNBQTZDO0FBR2xGLFNBQVMsdUJBQXVCO0FBQzlCLFNBQU87QUFBQSxJQUNMLE1BQU07QUFBQSxJQUVOLFVBQVUsS0FBSyxJQUFJO0FBQ2pCLFVBQUksR0FBRyxTQUFTLFlBQVksR0FBRztBQUM3QixlQUFPLElBQUksUUFBUSxnQkFBZ0IsZ0JBQWdCO0FBQUEsTUFDckQsV0FBVyxHQUFHLFNBQVMsV0FBVyxHQUFHO0FBQ25DLGVBQU8sSUFBSSxXQUFXLGdCQUFnQixnQkFBZ0I7QUFBQSxNQUN4RDtBQUFBLElBQ0Y7QUFBQSxFQUNGO0FBQ0Y7QUFFQSxJQUFPLHNCQUFRLGFBQWE7QUFBQSxFQUMxQixTQUFTO0FBQUEsSUFDUCxRQUFRO0FBQUEsTUFDTixPQUFPO0FBQUEsUUFDTDtBQUFBLFFBQ0E7QUFBQSxRQUNBO0FBQUEsUUFDQSxHQUFHO0FBQUEsUUFDSCxHQUFHO0FBQUEsUUFDSCxHQUFHO0FBQUEsUUFDSCxHQUFHO0FBQUEsUUFDSDtBQUFBO0FBQUEsUUFDQSxHQUFHO0FBQUEsUUFDSCxHQUFHO0FBQUEsUUFDSCxHQUFHO0FBQUEsUUFDSCxHQUFHO0FBQUEsTUFDTDtBQUFBLE1BQ0EsU0FBUztBQUFBLElBQ1gsQ0FBQztBQUFBLElBQ0QsS0FBSztBQUFBLElBQ0wscUJBQXFCO0FBQUEsRUFDdkI7QUFDRixDQUFDOyIsCiAgIm5hbWVzIjogW10KfQo=

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Bulk operation redirection
 * @author Jerome Mouneyrac <jerome@moodle.com>
 */

YUI.add('moodle-local_hub-courses', function(Y) {
    var HUBCOURSESNAME = 'local_hub_courses';
    var HUBCOURSES = function() {
        HUBCOURSES.superclass.constructor.apply(this, arguments);
    }
    Y.extend(HUBCOURSES, Y.Base, {
        initializer : function(config) { //'config' contains the parameter values
            Y.all('select.menubulkselect').each(this.attach_hub_events, this);

            //hide the submit buttons
            Y.all('input.bulksubmitbutton').setStyle('display', 'none');
        },

        attach_hub_events : function(selectnode) {
            selectnode.on('change', this.submit_hub_action, this, selectnode);
        },

        submit_hub_action : function(e, selectnode){

            //retrieve parameters (which checkboxes is checked and what kind of action is trigger)
            var theinputs = Y.all('input.hubmanagecoursecheckbox');

            //retrieve bulk action
            var bulkaction = 'bulkselect='+Y.one('select.menubulkselect').get('value');

            //build the course ids param url
            var inputssize = theinputs.size();
            var courseidparams = '';
            for ( var i=0; i<inputssize; i++ )
            {
                if (theinputs.item(i).get("checked")) {
                    courseidparams += '&'+theinputs.item(i).get("name")+'='+theinputs.item(i).get("value");
                }
            }

            if (Y.one('select.menubulkselect').get('value') == '') {
                //do nothing, the user just reset the select box
            } else if (courseidparams == '') {
                alert(M.str.local_hub.nocourseselected);
            } else {
                //do a redirection to the confirmation page of the select action
                var redirectionurl = M.cfg.wwwroot + '/'+this.get('scriptname')+'?' + bulkaction
                    + '&sesskey=' + M.cfg.sesskey +  courseidparams;
                window.location = redirectionurl;
            }

        }
    }, {
        NAME : HUBCOURSESNAME, //module name is something mandatory.
                                //It should be in lower case without space
                                //as YUI use it for name space sometimes.
        ATTRS : {
                 scriptname : {}
        } // Attributs are the parameters sent when the $PAGE->requires->yui_module calls the module.
          // Here you can declare default values or run functions on the parameter.
          // The param names must be the same as the ones declared
          // in the $PAGE->requires->yui_module call.
    });
    M.local_hub = M.local_hub || {}; //this line use existing name path if it exists, ortherwise create a new one.
                                                 //This is to avoid to overwrite previously loaded module with same name.
    M.local_hub.init_hubcourses = function(config) { //'config' contains the parameter values
        return new HUBCOURSES(config); //'config' contains the parameter values
    }
  }, '@VERSION@', {
      requires:['base', 'node']
  });
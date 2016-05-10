import React, { Component } from 'react';
import config from 'config';
import Widget from '../../Widget';
import CmsVulnerabilitiesWidget from './CmsVulnerabilitiesWidget';

class CmsVulnerabilities extends Component {

  constructor(props) {
    super(props);
    Widget.registerWidget(this, props);
  }

  componentWillMount () {
    Widget.getPayload(this, config.apiUrl + 'vulnerabilities', this.processData);
  }

  processData (data) {
    let returns = {
      core: {},
      plugins: []
    }
    console.log(data);
    data.map((module) => {
      if(module.type && module.type === 'application') {
        returns['core'] = module;
      }
      else {
        returns['plugins'].push(module);
      }
    });
    return returns;
  }

  render () {

    let widget = this.props.widget;
    
    // Return loading if not set
    if(!widget || widget.status !== 'loaded') {
      return Widget.loadingDisplay();
    }

    if(this.props.display === 'page') {
      // @TODO?
      return (
        <div>CmsVulnerabilitiesPage</div>
      )
    }
    else {
      return (
        <CmsVulnerabilitiesWidget 
          header={Widget.titleSection('Known vulnerabilities', false, 'h3')}
          core={widget.data.core}
          plugins={widget.data.plugins} />
      )
    }
  }
}

CmsVulnerabilities.propTypes = Widget.propTypes();
CmsVulnerabilities.defaultProps = Widget.defaultProps();

export default Widget.connect(CmsVulnerabilities);